<?php

namespace Modules\Student\Http\Controllers\Student;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Modules\Payment\Entities\Payment;
use Modules\Student\Entities\StudentIntakeCourse;
use Modules\Student\Entities\StudentIntakeCourseFee;
use Modules\Student\Entities\StudentIntakeCourseFeeInstallment;
use Modules\Student\Entities\StudentIntakeUnitFee;
use Modules\Student\Entities\StudentIntakeUnitFeePayment;
use Modules\Student\Entities\StudentIntakeUnitFeePaymentStripe;
use Stripe\Charge;
use Stripe\Stripe;

class FeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (feeSetting('fee_module') == 'yes') {
            activityLog('Student', 'Opened Fee menu from web');
            $id = Auth::guard('student')->user()->id;
            $fees = StudentIntakeCourseFee::where('student_id', $id)->orderBy('id', 'asc')->get();
            foreach ($fees as $key => $value) {
                $fees[$key]['installments'] = StudentIntakeCourseFeeInstallment::where('student_intake_course_fee_id', $value->id)->orderBy('due_date')->get();
                $p_amount = [];
                $r_amount = [];
                foreach ($fees[$key]['installments'] as $installment) {
                    $paid = $installment->studentIntakeCourseFeePayment;
                    if ($paid) {
                        $p_amount[] = $paid->paid_amount;
                        $r_amount[] = $paid->remaining_amount;
                        if ($paid->paymentRefund && $paid->paymentRefund->reinstate == 1) {
                            $ri_amount[] = $paid->paid_amount;
                        } else {
                            $ri_amount[] = 0;
                        }
                    } else {
                        $p_amount[] = 0;
                        $r_amount[] = 0;
                        $ri_amount[] = 0;
                    }
                    if ($installment->status == 3) {
                        $rf_amount[] = $installment->amount;
                    } else {
                        $rf_amount[] = 0;
                    }
                }
                if ($value->enrollment_fee_wavier == 1) {
                    $fees[$key]['enrollment_fee'] = 0;
                } else {
                    $fees[$key]['enrollment_fee'] = $value->enrollment_fee;
                }
                if ($value->material_fee_wavier == 1) {
                    $fees[$key]['material_fee'] = 0;
                } else {
                    $fees[$key]['material_fee'] = $value->material_fee;
                }
                $fee = $value->fee;
                $fees[$key]['total_fee'] = $fees[$key]['enrollment_fee'] + $fees[$key]['material_fee'] + $fee;
                $fees[$key]['total_intsallment'] = StudentIntakeCourseFeeInstallment::where('student_intake_course_fee_id', $value->id)->sum('amount');
                $fees[$key]['paid_installment'] = array_sum($p_amount);
                $fees[$key]['remaining'] = $fees[$key]['total_fee'] - ($fees[$key]['total_intsallment'] - array_sum($r_amount) - array_sum($ri_amount));
                $fees[$key]['remaining_installment'] = StudentIntakeCourseFeeInstallment::where('student_intake_course_fee_id', $value->id)->where('status', 1)->sum('amount');
                $fees[$key]['refunded_amount'] = array_sum($rf_amount);
                $fees[$key]['student_intake_course'] = StudentIntakeCourse::where('student_id', $id)->where('intake_course_id', $value->intake_course_id)->first();
            }
            return view('student::student.fee.index', compact('fees'));
        } else {
            return abort(404);
        }
    }

    public function unitFee()
    {
        if (feeSetting('unit_wise_fee') == 'yes') {
            activityLog('Student', 'Opened Unit Fee menu from web');
            $id = Auth::guard('student')->user()->id;
            $fees = StudentIntakeUnitFee::where('student_id', $id)->get();
            return view('student::student.unit-fee.index', compact('fees'))->with('no', 1);
        } else {
            return abort(404);
        }
    }

    public function unitFeePayment($id)
    {
        if (feeSetting('unit_wise_fee') == 'yes') {
            $fee = StudentIntakeUnitFee::findorfail($id);
            $stripe_key = getStripeKey('stripe_key');
            $payments = Payment::where('status', 1)->get();
            activityLog('Student', 'Opened Unit Fee payment page from web');
            return view('student::student.unit-fee.payment', compact('fee', 'stripe_key', 'payments'));
        } else {
            return abort(404);
        }
    }

    public function unitFeePay(Request $request, $id)
    {
        $data = $request->all();
        $fee = StudentIntakeUnitFee::findorfail($id);
        $amount = $fee->fee * 100;
        $data['student_intake_unit_fee_id'] = $id;
        $data['fee_amount'] = $amount;
        if ($request->has('receipt')) {
            $data['receipt'] = uploadFile(request()->receipt, 'images/student/unit_fee', 'documents', 'receipt');
        }
        if ($data['paid_date'] == NULL) {
            $data['paid_date'] = date('Y-m-d');
        }
        $payment = StudentIntakeUnitFeePayment::create($data);
        if ($request->payment_type == 'Stripe') {
            Stripe::setApiKey(getStripeKey('stripe_secret'));

            $description = 'Payment of ' . userName('Student', $fee->student_id) . ' for unit: ' . $fee->intakeUnit->unit->name . ' ,fee: ' . $fee->name;

            $charge = Charge::create([
                "amount" => $amount,
                "currency" => "aud",
                "source" => $request->stripeToken,
                "description" => $description
            ]);

            $data['student_intake_unit_fee_payment_id'] = $payment->id;
            $data['stripe_id'] = $charge['id'];
            StudentIntakeUnitFeePaymentStripe::create($data);
        }
        $fee->update(['status' => 2]);

        // $ids[] = $id;
        // $student_fees = StudentIntakeUnitFeePayment::whereIn('student_intake_unit_fee_id', $ids)->get();
        // $to_name = userName('Student', $fee->student_id);
        // $to_email = $fee->student->email;
        // $course_name = $fee->intakeUnit->intakeCourse->course->course_name;
        // $data = [
        //     'name' => $to_name,
        //     'course_name' => $course_name,
        //     'fees' => $student_fees,
        // ];
        // Mail::send('student::student.unit-fee.email', $data, function ($message) use ($to_name, $to_email) {
        //     $message->to($to_email, $to_name)
        //         ->subject('Receipt for unit fee payment');
        //     $message->from(config('mail.from.address'), 'LMS');
        // });
        activityLog('Student',  $fee->name . ' has been paid from web');
        return redirect()->route('student.fee.unit.index')->with('success', 'Unit fee has been paid');
    }

    public function mutltiUnitFeePayment(Request $request)
    {
        $data = $request->all();
        foreach ($data['fees'] as $key => $value) {
            $fee = StudentIntakeUnitFee::find($value);
            $amount[] = $fee->fee;
        }
        $total_amount = array_sum($amount);
        $ids = implode(", ", $data['fees']);
        $stripe_key = getStripeKey('stripe_key');
        $payments = Payment::where('status', 1)->get();
        return view('student::student.unit-fee.multiple-pay', compact('total_amount', 'ids', 'stripe_key', 'payments'));
    }

    public function mutltiUnitFeePay(Request $request)
    {
        $data = $request->all();
        $ids = explode(", ", $data['ids']);

        $fee0 = StudentIntakeUnitFee::find($ids[0]);

        if ($request->has('receipt')) {
            $data['receipt'] = uploadFile(request()->receipt, 'images/student/unit_fee', 'documents', 'receipt');
        }
        if ($data['paid_date'] == NULL) {
            $data['paid_date'] = date('Y-m-d');
        }
        if ($request->payment_type == 'Stripe') {
            Stripe::setApiKey(getStripeKey('stripe_secret'));

            $description = 'Payment of ' . userName('Student', $fee0->student_id) . ' for muliple units has been made';

            $charge = Charge::create([
                "amount" => $data['amount'],
                "currency" => "aud",
                "source" => $request->stripeToken,
                "description" => $description
            ]);
        }
        foreach ($ids as $key => $value) {
            $fee = StudentIntakeUnitFee::find($value);
            $data['student_intake_unit_fee_id'] = $fee->id;
            $data['fee_amount'] = $fee->fee;
            $payment = StudentIntakeUnitFeePayment::create($data);
            if ($request->payment_type == 'Stripe') {
                $data['student_intake_unit_fee_payment_id'] = $payment->id;
                $data['stripe_id'] = $charge['id'];
                StudentIntakeUnitFeePaymentStripe::create($data);
            }
            $fee->update(['status' => 2]);
        }
        // $student_fees = StudentIntakeUnitFeePayment::whereIn('student_intake_unit_fee_id', $ids)->get();
        // $to_name = userName('Student', $fee0->student_id);
        // $to_email = $fee0->student->email;
        // $course_name = $fee0->intakeUnit->intakeCourse->course->course_name;
        // $data = [
        //     'name' => $to_name,
        //     'course_name' => $course_name,
        //     'fees' => $student_fees,
        // ];
        // Mail::send('student::student.unit-fee.email', $data, function ($message) use ($to_name, $to_email) {
        //     $message->to($to_email, $to_name)
        //         ->subject('Receipt for unit fee payment');
        //     $message->from(config('mail.from.address'), 'LMS');
        // });
        return redirect()->route('student.fee.unit.index')->with('success', 'Successfully paid');
    }
}
