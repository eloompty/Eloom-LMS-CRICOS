<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Address\Entities\Address;
use Modules\Assignment\Entities\AssignmentSubmission;
use Modules\Attendance\Entities\Attendance;
use Modules\Email\Entities\EmailUser;
use Modules\OnlineClass\Entities\OnlineClassGroupStudent;
use Modules\Social\Entities\Social;
use Modules\Student\Entities\Student;
use Modules\Student\Entities\StudentAgent;
use Modules\Student\Entities\StudentCredit;
use Modules\Student\Entities\StudentDeliverySite;
use Modules\Student\Entities\StudentDevice;
use Modules\Student\Entities\StudentDocument;
use Modules\Student\Entities\StudentIntakeCourse;
use Modules\Student\Entities\StudentIntakeCourseCompetence;
use Modules\Student\Entities\StudentIntakeCourseCompetenceCertificate;
use Modules\Student\Entities\StudentIntakeCourseFee;
use Modules\Student\Entities\StudentIntakeCourseFeeInstallment;
use Modules\Student\Entities\StudentIntakeCourseFeeInstallmentPayment;
use Modules\Student\Entities\StudentIntakeCourseFeeInstallmentPaymentCommission;
use Modules\Student\Entities\StudentIntakeCourseFeeInstallmentPaymentNote;
use Modules\Student\Entities\StudentIntakeCourseFeeInstallmentPaymentRefund;
use Modules\Student\Entities\StudentIntakeUnit;
use Modules\Student\Entities\StudentIntakeUnitFee;
use Modules\Student\Entities\StudentIntakeUnitFeePayment;
use Modules\Student\Entities\StudentIntakeUnitFeePaymentStripe;
use Modules\Student\Entities\StudentNote;
use Modules\Student\Entities\StudentOffer;
use Modules\Student\Entities\StudentPasswordReset;
use Modules\Student\Entities\StudentPayment;
use Modules\Student\Entities\StudentPaymentCommission;
use Modules\Student\Entities\StudentTemplate;
use Modules\Student\Entities\StudentTemplateData;

class StudentDeleteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $student_id = $request->student;
        $student = Student::find($student_id);
        Address::where('type', 'student')->where('type_id', $student_id)->delete();
        StudentDeliverySite::where('student_id', $student_id)->delete();
        StudentAgent::where('student_id', $student_id)->delete();
        Social::where('user_type', 'Student')->where('user_id', $student_id)->delete();
        StudentDevice::where('student_id', $student_id)->delete();
        AssignmentSubmission::where('student_id', $student_id)->delete();
        StudentNote::where('student_id', $student_id)->delete();
        Attendance::where('student_id', $student_id)->delete();
        $intakes = StudentIntakeCourse::where('student_id', $student_id)->get();
        foreach ($intakes as $key => $value) {
            StudentIntakeUnit::where('student_intake_course_id', $value->student_intake_course_id)->delete();
            $completences = StudentIntakeCourseCompetence::where('student_intake_course_id', $value->student_intake_course_id)->get();
            foreach ($completences as $comptence) {
                StudentIntakeCourseCompetenceCertificate::where('student_intake_course_competence_id', $comptence->student_intake_course_competence_id)->delete();
                $comptence->delete();
            }
            $value->delete();
        }
        $intakeCouerseFees = StudentIntakeCourseFee::where('student_id', $student_id)->get();
        foreach ($intakeCouerseFees as $intakeCourseFee) {
            $feeInstallments = StudentIntakeCourseFeeInstallment::where('student_intake_course_fee_id', $intakeCourseFee->id)->get();
            foreach ($feeInstallments as $feeInstallment) {
                $feeInstallmentPyament = StudentIntakeCourseFeeInstallmentPayment::where('student_intake_course_fee_installment_id', $feeInstallment->id)->first();
                if ($feeInstallmentPyament) {
                    StudentIntakeCourseFeeInstallmentPaymentNote::where('student_intake_course_fee_installment_payment_id', $feeInstallmentPyament->id)->delete();
                    StudentIntakeCourseFeeInstallmentPaymentCommission::where('student_intake_course_fee_installment_payment_id', $feeInstallmentPyament->id)->delete();
                    StudentIntakeCourseFeeInstallmentPaymentRefund::where('student_intake_course_fee_installment_payment_id', $feeInstallmentPyament->id)->delete();
                    $feeInstallmentPyament->delete();
                }
            }
        }
        $intakeUnitFees = StudentIntakeUnitFee::where('student_id', $student_id)->get();
        foreach ($intakeUnitFees as $intakeUnitFee) {
            $unit_fee = StudentIntakeUnitFeePayment::where('student_intake_unit_fee_id', $intakeUnitFee->student_intake_unit_fee_id)->first();
            if ($unit_fee) {
                StudentIntakeUnitFeePaymentStripe::where('student_intake_unit_fee_payment_id', $unit_fee->id)->delete();
                $unit_fee->delete();
            }
            $intakeUnitFee->delete();
        }
        EmailUser::where('user_id', $student_id)->where('user_type', 'Student')->delete();
        $payments = StudentPayment::where('student_id', $student_id)->get();
        foreach ($payments as $payment) {
            StudentPaymentCommission::where('student_payment_id', $payment->id)->delete();
            $payment->delete();
        }
        $templates = StudentTemplate::where('student_id', $student_id)->get();
        foreach ($templates as $template) {
            StudentTemplateData::where('student_template_id', $template->id)->delete();
            $template->delete();
        }
        StudentOffer::where('student_id', $student_id)->delete();
        StudentCredit::where('student_id', $student_id)->delete();
        StudentDocument::where('student_id', $student_id)->delete();
        StudentPasswordReset::where('email', $student->email)->delete();
        OnlineClassGroupStudent::where('student_id', $student_id)->delete();
        $student->delete();
        return response()->json(['success' => true, 'message' => 'Student deleted successfully']);
    }
}
