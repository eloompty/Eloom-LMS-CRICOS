<?php

namespace Modules\Student\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Company\Entities\Company;
use Modules\Condition\Entities\Condition;
use Modules\Credit\Entities\Credit;
use Modules\Intake\Entities\IntakeCourse;
use Modules\Student\Entities\Student;
use Modules\Student\Entities\StudentIntakeCourse;
use Modules\Student\Entities\StudentIntakeCourseFee;
use Modules\Student\Entities\StudentOffer;
use PhpOffice\PhpWord\TemplateProcessor;
use Dompdf\Dompdf;
use Dompdf\Options;
use Modules\OfferTemplate\Entities\OfferTemplate;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Modules\Student\Entities\StudentAgent;

class StudentOfferLetterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user');
    }

    /**
     * Display a listing of the offer letter.
     * @return Renderable
     */
    public function index($id)
    {
        $student = Student::find($id);
        if (checkRole('student', 'view') == true) {
            activityLog('Admin', 'Opened Student Offer Letter Menu');
            $letters = StudentOffer::where('student_id', $id)->orderby('id', 'desc')->get();
            foreach ($letters as $key => $value) {
                $intake_course_ids = explode(',', $value->intake_course_ids);
                $intake_courses = [];
                foreach ($intake_course_ids as $id) {
                    $intake_course = IntakeCourse::find($id);
                    $intake_courses[] = $intake_course->course->course_name . '(' . $intake_course->intake->name . ')';
                }
                $letters[$key]['intakes'] = implode(", ", $intake_courses);
            }
            return view('student::offer-letter.index', compact('letters', 'student'))->with('no', 1);
        } else {
            return abort(404);
        }
    }

    /**
     * Show the form for creating a new offer letter.
     * @return Renderable
     */
    public function create($id)
    {
        $student = Student::find($id);
        if (checkRole('student', 'add') == true) {
            activityLog('Admin', 'Opened Student Offer Letter Create Page');
            $intake_courses = $student->intake->where('status', 1);
            $conditions = Condition::where('status', 1)->get();
            $credits = Credit::where('status', 1)->get();
            $templates = OfferTemplate::where('status', 1)->get();
            $date = date('Y-m-d');
            return view('student::offer-letter.create', compact('student', 'templates', 'intake_courses', 'conditions', 'credits', 'date'));
        } else {
            return abort(404);
        }
    }

    /**
     * Store a newly created offer letter in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $id)
    {
        $data = $request->all();
        $data['student_id'] = $id;
        $studentIntakeCourses = $data['intake_course_ids'];
        $data['intake_course_ids'] = (implode(",", $data['intake_course_ids']));

        if (isset($data['condition'])) {
            $condition = Condition::find($data['condition']);
            $data['condition_title'] = $condition->title;
            $data['condition_description'] = $condition->description;
        }

        if (isset($data['credit'])) {
            $credit = Credit::find($data['credit']);
            $data['credit_title'] = $credit->title;
            $data['credit_description'] = $credit->description;
        }

        if (isset($data['template']))  {
            // $templates = OfferTemplate::findorfail('id', $data['template']);
            $data['offer_template_id'] = $data['template'];
        }

        $offer_number = StudentOffer::count() + 1;
        $student = Student::findorfail($id);
        $student_agent = StudentAgent::where('student_id', $student->$id)->first(); 
        if ($student_agent) {
            $student_agent_company_name = $student_agent->agent->company_name;
            $student_agent_address = $student_agent->agent->address;
            $student_agent_phone = $student_agent->agent->mobile;
            $student_agent_website = $student_agent->agent->url;
        }else {
            $student_agent_company_name = "";
            $student_agent_address = "";
            $student_agent_phone = "";
            $student_agent_website = "";
        }
        $student_courses = StudentIntakeCourse::where('student_id', $id)->where('status', 1)->whereIn('intake_course_id', $studentIntakeCourses)->get();
        $student_fees = StudentIntakeCourseFee::where('student_id', $id)->where('status', 1)->whereIn('intake_course_id', $studentIntakeCourses)->get();

        $studentID = $student->id_no;
        $studentName = userName('Student', $id);
        $studentAddress = fullAddress('Student', $id);
        $studentPassportNumber = $student->passport_no;
        $studentDOB = dateFormat($student->date_of_birth);
       

        $offerExpiryDate = dateFormat($data['expiry_date']);
        $offerIssueDate = dateFormat($data['issue_date']);
        if (isset($data['condition']) && $data['condition'] != null)  {
            $condition = Condition::find($data['condition']);
            $data['condition_title'] = $condition->title;
            $data['condition_description'] = $condition->description;
            $offerCondition = $data['condition_description'];
        }else {
            $offerCondition = "";
        }

        if (isset($data['credit']) && $data['credit'] != null)  {
            $credit = Credit::find($data['credit']);
            $data['credit_title'] = $credit->title;
            $data['credit_description'] = $credit->description;
            $offerCredit = $data['credit_description'];
        }else {
            $offerCredit = "";
        }
         // Initialize variables
         $course_table_rows = [];
         $course_cricos_codes = [];

         // Start table header
         $course_table_header = "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 10px;'>
             <thead>
                 <tr style='font-size:8px;'>
                     <th style='padding: 5px; text-align:center'><strong>Course Code</strong></th>
                     <th style='padding: 5px; text-align:center'><strong>Course Name</strong></th>
                     <th style='padding: 5px; text-align:center'><strong>CRICOS Course Code</strong></th>
                     <th style='padding: 5px; text-align:center'><strong>Start Date</strong></th>
                     <th style='padding: 5px; text-align:center'><strong>Finish Date</strong></th>
                     <th style='padding: 5px; text-align:center'><strong>Duration</strong></th>
                 </tr>
             </thead>
             <tbody>";

        // Loop through each unit and assign rows accordingly
        foreach ($student_courses as $course) {
            $courseCode = $course->intakeCourse->course->course_code;
            $courseName = $course->intakeCourse->course->course_name;
            $courseCRICOSCode = $course->intakeCourse->course->cricos_code;
            $course_cricos_codes[] = $courseCRICOSCode;
            $courseStartDate = dateFormat($course->intakeCourse->starting_date);
            $courseEndDate = dateFormat($course->intakeCourse->ending_date);
            $courseDuration = $course->intakeCourse->duration;
            // Append row
            $course_table_rows[] = "<tr style='font-size:8px;'>
                <td style='padding: 5px; text-align:center'>$courseCode</td>
                <td style='padding: 5px; text-align:center'>$courseName</td>
                <td style='padding: 5px; text-align:center'>$courseCRICOSCode</td>
                <td style='padding: 5px; text-align:center'>$courseStartDate</td>
                <td style='padding: 5px; text-align:center'>$courseEndDate</td>
                <td style='padding: 5px; text-align:center'>$courseDuration</td>
            </tr>";
        }
        $courseCodesCRICOS = (implode(",", $course_cricos_codes));
        
        $course_table_body = (implode(",", $course_table_rows));
        $course_table = $course_table_header . $course_table_body . "</tbody></table>";

        $courseFeesTablePlaceholders = [];
        $student_courses =  StudentIntakeCourse::findorfail($student_courses[0]->id);
        $course_orientation_date = dateFormat($student_courses->intakeCourse->intake->orientation_date);
        $course_start_date = dateFormat($student_courses->intakeCourse->starting_date);
        $course_end_date = dateFormat($student_courses->intakeCourse->ending_date);

        $course_fees_table_first = [];
        $total_enrollment_fees = 0;
        $total_tuition_fees = 0;
        $total_material_fees = 0;
        $total_course_fees = 0;
        
        $total_first_installments = [];
        $total_first_installment_initial_fees = 0;
        $installments = [];

        foreach($student_fees as $fee) {
            $courseName = $fee->intakeCourse->course->course_name;
            $total_enrollment_fees += $fee->enrollment_fee;
            $total_tuition_fees += $fee->fee;
            $total_material_fees += $fee->material_fee;
            $total_course_fees += $fee->enrollment_fee + $fee->fee + $fee->material_fee;
            $total_first_installment_initial_fees += $fee->installments->first()->amount;
            $total_first_installments[] = $fee->installments->first()->studentIntakeCourseFee->intakeCourse->course->course_name . ': $'. $fee->installments->first()->amount;
            $a = $fee->installments->first();
            // $installments[] = $fee->installments->where('name', '!=', 'First Installment');
            // Exclude the first installment ($a) from the installments array
            $installments[] = $fee->installments->reject(fn($installment) => $installment->id === $a->id);


            $course_fees_table_first [] = "
                <tr style='font-size:8px;'>
                    <td>
                    <span>Enrolment Application Fee (non-refundable)
                    </td>
                    <td>
                    <span>$ $fee->enrollment_fee</span>
                    </td>
                </tr>
                <tr style='font-size:8px;'>
                    <td>
                    <span>Tuition Fee $courseName</span>
                    </td>
                    <td>
                    <span>$ $fee->fee </span>
                    </td>
                </tr>
                <tr style='font-size:8px;'>
                    <td>
                    <span>Non-Tuition Fee: Materials Fee ($courseName)</span>
                    </td>
                    <td>
                    <span>$ $fee->material_fee</span>
                    </td>
                </tr>";
        }
        $fees_start_table = "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 10px;'>
            <tbody>";
        $osch_fees = "
                <tr style='font-size:8px;'>
                    <td>
                    <span>Total Course Fees</span>
                    </td>
                    <td>
                    <span>$ $total_course_fees</span>
                    </td>
                </tr>
                <tr style='font-size:8px;'>
                    <td>
                    <span>Overseas Student Health Cover (OSHC)</span>
                    </td>
                    <td>
                    <span>As per the Insurance Company</span>
                    </td>
                </tr>
                </tbody>
            </table>";
        $course_table_fees_data = (implode(",", $course_fees_table_first));

        $performatTable = "<p style='font-size:8px; padding-bottom: 2px;'><strong>Proforma Student Invoice</strong></p><table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 10px;'>
                            <tbody>
                                <tr style='font-size:8px; padding: 5px; text-align:center'>
                                    <td>
                                    <strong>Enrollment Fee </strong>
                                    </td>
                                    <td>
                                    <strong>Tuition Fee </strong>
                                    </td>
                                    <td>
                                    <strong>OSHC </strong>
                                    </td>
                                    <td>
                                    <strong>Others (Student ID, Material Fee)</strong>
                                    </td>
                                    <td>
                                    <strong>Discounts </strong>
                                    </td>
                                    <td>
                                    <strong>Total Summary</strong>
                                    </td>
                                </tr>
                                <tr style='font-size:8px; text-align:center'>
                                    <td>
                                    $ $total_enrollment_fees
                                    </td>
                                    <td>
                                    $ $total_tuition_fees
                                    </td>
                                    <td>
                                    TBA
                                    </td>
                                    <td>
                                    $ $total_material_fees
                                    </td>
                                    <td>
                                    $.00</p>
                                    </td>
                                    <td>
                                    $ $total_course_fees
                                    </td>
                                </tr>
                            </tbody>
                        </table>";

        $total_non_tution_fees = $total_enrollment_fees + $total_material_fees;
        $total_first_installment = $total_first_installment_initial_fees - $total_non_tution_fees;
        $first_installment_details = (implode(", ", $total_first_installments));

        $fees_first_installment = "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 1px;'>
                                <tr style='font-size:8px;'>
                                    <td> &nbsp;<b>Payment No.</b></td>
                                    <td> &nbsp;<b>Due Date</b></td>
                                    <td> &nbsp;<b>Tuition Fees</b></td>
                                    <td> &nbsp;<b>Non-Tuition Fees</b></td>
                                    <td> &nbsp;<b>Total Amount</b></td>
                                </tr>
                                <tr style='font-size:8px;'>
                                    <td>1</td>
                                    <td>Due on acceptance of offer </td>
                                    <td>
                                        <p>Total: $ $total_first_installment; <br />
                                         $first_installment_details </p>
                                    </td>
                                    <td> $total_non_tution_fees </td>
                                    <td> $total_first_installment_initial_fees </td>
                                </tr>
                            </table>";

        $course_table_fees = $fees_start_table . $course_table_fees_data . $osch_fees . $performatTable . $fees_first_installment;
        
        // Start table header
        $course_fees_installment_table_header = "
        <span style='font-size:8px; text-align:center'><strong> Payment Schedule </strong></span><table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 5px;'>
        <thead>
            <tr style='font-size:8px;'>
                <th style='padding: 5px; text-align:center'><strong>Payment No.</strong></th>
                <th style='padding: 5px; text-align:center'><strong>Course</strong></th>
                <th style='padding: 5px; text-align:center'><strong>Due Date</strong></th>
                <th style='padding: 5px; text-align:center'><strong>Tuition Fees </strong></th>
                <th style='padding: 5px; text-align:center'><strong>Non-Tuition Fees </strong></th>
                <th style='padding: 5px; text-align:center'><strong>Discounts</strong></th>
                <th style='padding: 5px; text-align:center'><strong>Total</strong></th>
            </tr>
        </thead>
        <tbody>";

        $index = 2; // Start index
        $installment_rows = []; // Store table rows

        foreach ($installments as $key => $installment) {
            foreach ($installment as $number => $value) {
                $course_code = $value->studentIntakeCourseFee->intakeCourse->course->course_code;
                $course_name = $value->studentIntakeCourseFee->intakeCourse->course->course_name;
                $due_date = dateFormat($value->due_date);
                $amount = $value->amount; // Format amount as currency

                // Store row in an array
                $installment_rows[] = "
                                <tr style='font-size:8px;'>
                                    <td> $index</td>
                                    <td> $course_code $course_name</td>
                                    <td> $due_date</td>
                                    <td> \$$amount</td>
                                    <td> \$0</td>
                                    <td'> \$0</td>
                                    <td> \$$amount</td>
                                </tr>
                            ";
                $index++; // Increment index
            }
        }

        // If no installments exist, create 3 blank rows
        if (empty($installment_rows)) {
            $installment_rows[] = "
                            <tr style='font-size:8px; text-align:center;'>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                            </tr>
                        ";
            for ($i = 1; $i <= 3; $i++) {
                $installment_rows[] = "
                            <tr style='font-size:8px; text-align:center;'>
                                <td>&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            </tr>
                        ";
            }
        }
        
        $final_installments_rows = implode(", ", $installment_rows);
        $final_installments_table = $course_fees_installment_table_header . $final_installments_rows . "</tbody></table>" ;

        // Replace placeholders with actual values
        $placeholders = array_merge([
            '{{$student_name}}' => e($studentName),
            '{{$student_address}}' => e($studentAddress),
            '{{$student_id}}' => e($studentID),
            '{{$student_passport_number}}' => e($studentPassportNumber),
            '{{$student_date_of_birth}}' => $studentDOB,
            '{{$course_codes}}' => $courseCodesCRICOS,
            '{{$course_table}}' => $course_table,
            '{{$date_of_issue}}' => $offerIssueDate,
            '{{$offer_number}}' => $offer_number,
            '{{$offer_issue_date}}' => $offerIssueDate,
            '{{$offer_expiry_date}}' => $offerExpiryDate,
            '{{$offer_condition}}' => $offerCondition,
            '{{$offer_credit}}' => $offerCredit,
            '{{$course_orientation_date}}' => $course_orientation_date,
            '{{$course_start_date}}' => $course_start_date,
            '{{$course_end_date}}' => $course_end_date,
            '{{$course_fees}}' => $course_table_fees,
            '{{$course_fees_installments}}' => $final_installments_table,
            '{{$agent_name}}' => e($student_agent_company_name),
            '{{$agent_address}}' =>  e($student_agent_address),
            '{{$agent_phone}}' =>  e($student_agent_phone),
            '{{$agent_website}}' => e($student_agent_website),
        ], $courseFeesTablePlaceholders);

        $offerTemplate = OfferTemplate::findorfail($data['template']);
        $templateName = $offerTemplate->name;
        $filePath = public_path($offerTemplate->path);
        if (!File::exists($filePath)) {
            return view('certificatetemplate::edit', compact('template'));
        }
        $htmlContent = file_get_contents($filePath);
        $htmlContentNew = str_replace(array_keys($placeholders), array_values($placeholders), $htmlContent);

        // Ensure proper page breaks and formatting
        $htmlContentNew .= "
            <style>
                body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
                table { margin: 0; padding: 0; width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid black; padding: 5px; text-align: center; }
                .page-break { page-break-before: always; break-inside: avoid; }
            </style>
        ";

        // Save HTML for debugging (optional)
        $newTemplateName = time() . "-" . $studentName;
        $pathToSave = "templates/offer/students/" . $newTemplateName . ".pdf";
        $savedFilePath = public_path($pathToSave);

        if (!File::exists(public_path('templates/offer/students/'))) {
            File::makeDirectory(public_path('templates/offer/students/'), 0775, true, true);
        }
        // file_put_contents($savedFilePath, $htmlContentNew);

        // Generate PDF using Dompdf
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($htmlContentNew)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'dpi' => 80
            ]);

        // Define the file path for saving the PDF
        $pdfFilePath = str_replace('.html', '.pdf', $savedFilePath);

        // Save the generated PDF file to the specified path
        $pdf->save($pdfFilePath);
        $data['path'] = $pathToSave;

        StudentOffer::create($data);
        activityLog('Admin', 'Offer letter of ' . userName('Student', $id) . ' generated');
        return redirect()->route('admin.student.offer.letter.index', $id)->with('success', 'Offer letter has been created');
    }

    /**
     * Show the specified offer letter.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $letter = StudentOffer::findorfail($id);
        $student_course_ids = $intake_course_ids = explode(',', $letter->intake_course_ids);
        $studentCourses = StudentIntakeCourse::where('student_id', $letter->student_id)->whereIn('intake_course_id', $student_course_ids);
        $student_courses = $studentCourses->get();
        foreach ($student_courses as $key => $value) {
            $courses[] = $value->intakeCourse->course->course_name;
        }
        $course_name = implode(", ", $courses);
        $first_intake = $studentCourses->orderBy('starting_date', 'asc')->first();
        $student_fees = StudentIntakeCourseFee::where('student_id', $letter->student_id)->whereIn('intake_course_id', $student_course_ids)->get();
        $company = Company::first();
        return view('student::offer-letter.show', compact('letter', 'company', 'student_courses', 'student_fees'));
    }

    /**
     * Show the form for editing the specified offer letter.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $letter = StudentOffer::find($id);
        if (checkRole('student', 'edit') == true && $letter) {
            $intake_courses = $letter->student->intake->where('status', 1);
            $intake_course_ids = explode(',', $letter->intake_course_ids);
            $conditions = Condition::where('status', 1)->get();
            $credits = Credit::where('status', 1)->get();
            $templates = OfferTemplate::where('status', 1)->get();
            activityLog('Admin', 'Offer letter of ' . userName('Student', $letter->student_id) . ' edit page opened');
            return view('student::offer-letter.edit', compact('letter', 'templates', 'intake_courses', 'intake_course_ids', 'conditions', 'credits'));
        } else {
            return abort(404);
        }
    }

    /**
     * Update the specified offer letter in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        unset($data['_token']);
        $studentIntakeCourses = $data['intake_course_ids'];
        $data['intake_course_ids'] = (implode(",", $data['intake_course_ids']));

        if (isset($data['condition'])) {
            $condition = Condition::find($data['condition']);
            $data['condition_title'] = $condition->title;
            $data['condition_description'] = $condition->description;
        }
        if (isset($data['credit'])) {
            $credit = Credit::find($data['credit']);
            $data['credit_title'] = $credit->title;
            $data['credit_description'] = $credit->description;
        }
        $letter = StudentOffer::where('id', $id)->first();
        $template_id = $data['template'];
        $offer_number = $letter->id;
        $student = Student::findorfail($letter->student_id);
        $student_agent = StudentAgent::where('student_id', $letter->student_id)->first(); 
        if ($student_agent) {
            $student_agent_company_name = $student_agent->agent->company_name;
            $student_agent_address = $student_agent->agent->address;
            $student_agent_phone = $student_agent->agent->mobile;
            $student_agent_website = $student_agent->agent->url;
        }else {
            $student_agent_company_name = "";
            $student_agent_address = "";
            $student_agent_phone = "";
            $student_agent_website = "";
        }

        $student_courses = StudentIntakeCourse::where('student_id', $letter->student_id)->where('status', 1)->whereIn('intake_course_id', $studentIntakeCourses)->get();
        $student_fees = StudentIntakeCourseFee::where('student_id', $letter->student_id)->where('status', 1)->whereIn('intake_course_id', $studentIntakeCourses)->get();

        $studentID = $student->id_no;
        $studentName = userName('Student', $student->id);
        $studentAddress = fullAddress('Student', $student->id);
        $studentPassportNumber = $student->passport_no;
        $studentDOB = dateFormat($student->date_of_birth);
       

        $offerIssueDate = dateFormat($data['issue_date']);
        $offerExpiryDate = dateFormat($data['expiry_date']);
        if (isset($data['condition']) && $data['condition'] != null)  {
            $condition = Condition::find($data['condition']);
            $data['condition_title'] = $condition->title;
            $data['condition_description'] = $condition->description;
            $offerCondition = $data['condition_description'];
        }else {
            $offerCondition = "";
        }

        if (isset($data['credit']) && $data['credit'] != null)  {
            $credit = Credit::find($data['credit']);
            $data['credit_title'] = $credit->title;
            $data['credit_description'] = $credit->description;
            $offerCredit = $data['credit_description'];
        }else {
            $offerCredit = "";
        }
         // Initialize variables
         $course_table_rows = [];
         $course_cricos_codes = [];

         // Start table header
         $course_table_header = "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 10px;'>
             <thead>
                 <tr style='font-size:8px;'>
                     <th style='padding: 5px; text-align:center'><strong>Course Code</strong></th>
                     <th style='padding: 5px; text-align:center'><strong>Course Name</strong></th>
                     <th style='padding: 5px; text-align:center'><strong>CRICOS Course Code</strong></th>
                     <th style='padding: 5px; text-align:center'><strong>Start Date</strong></th>
                     <th style='padding: 5px; text-align:center'><strong>Finish Date</strong></th>
                     <th style='padding: 5px; text-align:center'><strong>Duration</strong></th>
                 </tr>
             </thead>
             <tbody>";

        // Loop through each unit and assign rows accordingly
        foreach ($student_courses as $course) {
            $courseCode = $course->intakeCourse->course->course_code;
            $courseName = $course->intakeCourse->course->course_name;
            $courseCRICOSCode = $course->intakeCourse->course->cricos_code;
            $course_cricos_codes[] = $courseCRICOSCode;
            $courseStartDate = dateFormat($course->intakeCourse->starting_date);
            $courseEndDate = dateFormat($course->intakeCourse->ending_date);
            $courseDuration = $course->intakeCourse->duration;
            // Append row
            $course_table_rows[] = "<tr style='font-size:8px;'>
                <td style='padding: 5px; text-align:center'>$courseCode</td>
                <td style='padding: 5px; text-align:center'>$courseName</td>
                <td style='padding: 5px; text-align:center'>$courseCRICOSCode</td>
                <td style='padding: 5px; text-align:center'>$courseStartDate</td>
                <td style='padding: 5px; text-align:center'>$courseEndDate</td>
                <td style='padding: 5px; text-align:center'>$courseDuration</td>
            </tr>";
        }
        $courseCodesCRICOS = (implode(",", $course_cricos_codes));
        
        $course_table_body = (implode(",", $course_table_rows));
        $course_table = $course_table_header . $course_table_body . "</tbody></table>";

        $courseFeesTablePlaceholders = [];
        $student_courses =  StudentIntakeCourse::findorfail($student_courses[0]->id);
        $course_orientation_date = dateFormat($student_courses->intakeCourse->intake->orientation_date);
        $course_start_date = dateFormat($student_courses->intakeCourse->starting_date);
        $course_end_date = dateFormat($student_courses->intakeCourse->ending_date);

        $course_fees_table_first = [];
        $total_enrollment_fees = 0;
        $total_tuition_fees = 0;
        $total_material_fees = 0;
        $total_course_fees = 0;
        
        $total_first_installments = [];
        $total_first_installment_initial_fees = 0;
        $installments = [];

        foreach($student_fees as $fee) {
            $courseName = $fee->intakeCourse->course->course_name;
            $total_enrollment_fees += $fee->enrollment_fee;
            $total_tuition_fees += $fee->fee;
            $total_material_fees += $fee->material_fee;
            $total_course_fees += $fee->enrollment_fee + $fee->fee + $fee->material_fee;
            $total_first_installment_initial_fees += $fee->installments->first()->amount;
            $total_first_installments[] = $fee->installments->first()->studentIntakeCourseFee->intakeCourse->course->course_name . ': $'. $fee->installments->first()->amount;
            $a = $fee->installments->first();
            // $installments[] = $fee->installments->where('name', '!=', 'First Installment');
            // Exclude the first installment ($a) from the installments array
            $installments[] = $fee->installments->reject(fn($installment) => $installment->id === $a->id);
            
            $course_fees_table_first [] = "
                <tr style='font-size:8px;'>
                    <td>
                    <span>Enrolment Application Fee (non-refundable)
                    </td>
                    <td>
                    <span>$ $fee->enrollment_fee</span>
                    </td>
                </tr>
                <tr style='font-size:8px;'>
                    <td>
                    <span>Tuition Fee $courseName</span>
                    </td>
                    <td>
                    <span>$ $fee->fee </span>
                    </td>
                </tr>
                <tr style='font-size:8px;'>
                    <td>
                    <span>Non-Tuition Fee: Materials Fee ($courseName)</span>
                    </td>
                    <td>
                    <span>$ $fee->material_fee</span>
                    </td>
                </tr>";
        }
        $fees_start_table = "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 10px;'>
            <tbody>";
        $osch_fees = "
                <tr style='font-size:8px;'>
                    <td>
                    <span>Total Course Fees</span>
                    </td>
                    <td>
                    <span>$ $total_course_fees</span>
                    </td>
                </tr>
                <tr style='font-size:8px;'>
                    <td>
                    <span>Overseas Student Health Cover (OSHC)</span>
                    </td>
                    <td>
                    <span>As per the Insurance Company</span>
                    </td>
                </tr>
                </tbody>
            </table>";
        $course_table_fees_data = (implode(",", $course_fees_table_first));

        $performatTable = "<p style='font-size:8px; padding-bottom: 2px;'><strong>Proforma Student Invoice</strong></p><table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 10px;'>
                            <tbody>
                                <tr style='font-size:8px; padding: 5px; text-align:center'>
                                    <td>
                                    <strong>Enrollment Fee </strong>
                                    </td>
                                    <td>
                                    <strong>Tuition Fee </strong>
                                    </td>
                                    <td>
                                    <strong>OSHC </strong>
                                    </td>
                                    <td>
                                    <strong>Others (Student ID, Material Fee)</strong>
                                    </td>
                                    <td>
                                    <strong>Discounts </strong>
                                    </td>
                                    <td>
                                    <strong>Total Summary</strong>
                                    </td>
                                </tr>
                                <tr style='font-size:8px; text-align:center'>
                                    <td>
                                    $ $total_enrollment_fees
                                    </td>
                                    <td>
                                    $ $total_tuition_fees
                                    </td>
                                    <td>
                                    TBA
                                    </td>
                                    <td>
                                    $ $total_material_fees
                                    </td>
                                    <td>
                                    $.00</p>
                                    </td>
                                    <td>
                                    $ $total_course_fees
                                    </td>
                                </tr>
                            </tbody>
                        </table>";

        $total_non_tution_fees = $total_enrollment_fees + $total_material_fees;
        $total_first_installment = $total_first_installment_initial_fees - $total_non_tution_fees;
        $first_installment_details = (implode(", ", $total_first_installments));

        $fees_first_installment = "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 1px;'>
                                <tr style='font-size:8px;'>
                                    <td> &nbsp;<b>Payment No.</b></td>
                                    <td> &nbsp;<b>Due Date</b></td>
                                    <td> &nbsp;<b>Tuition Fees</b></td>
                                    <td> &nbsp;<b>Non-Tuition Fees</b></td>
                                    <td> &nbsp;<b>Total Amount</b></td>
                                </tr>
                                <tr style='font-size:8px;'>
                                    <td>1</td>
                                    <td>Due on acceptance of offer </td>
                                    <td>
                                        <p>Total: $ $total_first_installment; <br />
                                         $first_installment_details </p>
                                    </td>
                                    <td> $total_non_tution_fees </td>
                                    <td> $total_first_installment_initial_fees </td>
                                </tr>
                            </table>";

        $course_table_fees = $fees_start_table . $course_table_fees_data . $osch_fees . $performatTable . $fees_first_installment;
        
        // Start table header
        $course_fees_installment_table_header = "
        <span style='font-size:8px; text-align:center'><strong> Payment Schedule </strong></span><table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 5px;'>
        <thead>
            <tr style='font-size:8px;'>
                <th style='padding: 5px; text-align:center'><strong>Payment No.</strong></th>
                <th style='padding: 5px; text-align:center'><strong>Course</strong></th>
                <th style='padding: 5px; text-align:center'><strong>Due Date</strong></th>
                <th style='padding: 5px; text-align:center'><strong>Tuition Fees </strong></th>
                <th style='padding: 5px; text-align:center'><strong>Non-Tuition Fees </strong></th>
                <th style='padding: 5px; text-align:center'><strong>Discounts</strong></th>
                <th style='padding: 5px; text-align:center'><strong>Total</strong></th>
            </tr>
        </thead>
        <tbody>";

        $index = 2; // Start index
        $installment_rows = []; // Store table rows

        foreach ($installments as $key => $installment) {
            foreach ($installment as $number => $value) {
                $course_code = $value->studentIntakeCourseFee->intakeCourse->course->course_code;
                $course_name = $value->studentIntakeCourseFee->intakeCourse->course->course_name;
                $due_date = dateFormat($value->due_date);
                $amount = $value->amount; // Format amount as currency

                // Store row in an array
                $installment_rows[] = "
                                <tr style='font-size:8px;'>
                                    <td> $index</td>
                                    <td> $course_code $course_name</td>
                                    <td> $due_date</td>
                                    <td> \$$amount</td>
                                    <td> \$0</td>
                                    <td'> \$0</td>
                                    <td> \$$amount</td>
                                </tr>
                            ";
                $index++; // Increment index
            }
        }

        // If no installments exist, create 3 blank rows
        if (empty($installment_rows)) {
            $installment_rows[] = "
                            <tr style='font-size:8px; text-align:center;'>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>N/A</td>
                            </tr>
                        ";
            for ($i = 1; $i <= 3; $i++) {
                $installment_rows[] = "
                            <tr style='font-size:8px; text-align:center;'>
                                <td>&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            </tr>
                        ";
            }
        }

        // Convert array to string for Blade rendering
        $final_installments_rows = implode(", ", $installment_rows);
        $final_installments_table = $course_fees_installment_table_header . $final_installments_rows . "</tbody></table>" ;

        // Replace placeholders with actual values
        $placeholders = array_merge([
            '{{$student_name}}' => e($studentName),
            '{{$student_address}}' => e($studentAddress),
            '{{$student_id}}' => e($studentID),
            '{{$student_passport_number}}' => e($studentPassportNumber),
            '{{$student_date_of_birth}}' => $studentDOB,
            '{{$course_codes}}' => $courseCodesCRICOS,
            '{{$course_table}}' => $course_table,
            '{{$offer_number}}' => $offer_number,
            '{{$date_of_issue}}' => $offerIssueDate,
            '{{$offer_issue_date}}' => $offerIssueDate,
            '{{$offer_expiry_date}}' => $offerExpiryDate,
            '{{$offer_condition}}' => $offerCondition,
            '{{$offer_credit}}' => $offerCredit,
            '{{$course_orientation_date}}' => $course_orientation_date,
            '{{$course_start_date}}' => $course_start_date,
            '{{$course_end_date}}' => $course_end_date,
            '{{$course_fees}}' => $course_table_fees,
            '{{$course_fees_installments}}' => $final_installments_table,
            '{{$agent_name}}' => e($student_agent_company_name),
            '{{$agent_address}}' =>  e($student_agent_address),
            '{{$agent_phone}}' =>  e($student_agent_phone),
            '{{$agent_website}}' => e($student_agent_website),
        ], $courseFeesTablePlaceholders);

        $offerTemplate = OfferTemplate::findorfail($data['template']);
        $templateName = $offerTemplate->name;
        $filePath = public_path($offerTemplate->path);
        if (!File::exists($filePath)) {
            return view('certificatetemplate::edit', compact('template'));
        }
        $htmlContent = file_get_contents($filePath);
        $htmlContentNew = str_replace(array_keys($placeholders), array_values($placeholders), $htmlContent);

        // Ensure proper page breaks and formatting
        $htmlContentNew .= "
            <style>
                body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
                table { margin: 0; padding: 0; width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid black; padding: 5px; text-align: center; }
                .page-break { page-break-before: always; break-inside: avoid; }
            </style>
        ";

        // Save HTML for debugging (optional)
        $newTemplateName = time() . "-" . $studentName;
        $pathToSave = "templates/offer/students/" . $newTemplateName . ".pdf";
        $savedFilePath = public_path($pathToSave);

        if (!File::exists(public_path('templates/offer/students/'))) {
            File::makeDirectory(public_path('templates/offer/students/'), 0775, true, true);
        }
        // file_put_contents($savedFilePath, $htmlContentNew);

        // Generate PDF using Dompdf
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($htmlContentNew)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'dpi' => 80
            ]);

        // Define the file path for saving the PDF
        $pdfFilePath = str_replace('.html', '.pdf', $savedFilePath);

        // Save the generated PDF file to the specified path
        $pdf->save($pdfFilePath);
        $data['path'] = $pathToSave;
        $letter->update($data);
        activityLog('Admin', 'Offer letter of ' . userName('Student', $letter->student_id) . ' updated');
        return redirect()->route('admin.student.offer.letter.index', $letter->student_id)->with('success', 'Offer letter has been updated');
    }

    /**
     * Remove the specified offer letter from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function exportWord($id)
    {
        $letter = StudentOffer::find($id);
        $company = Company::first();
        $templateProcessor = new TemplateProcessor('offer-templates/intership_offer.docx');
        $templateProcessor->setValue('student_name', userName('Student', $letter->student_id));
        $templateProcessor->setValue('company_name', $company->company_name);
        $templateProcessor->setValue('date', date('d/m/Y'));
        $templateProcessor->setValue('offer_signed_by_name', getSettingValue('offer_signed_by_name'));
        $templateProcessor->setValue('offer_signed_by_designation', getSettingValue('offer_signed_by_designation'));
        $fileName = $letter->id;
        $templateProcessor->saveAs($fileName . '.docx');
        return response()->download($fileName . '.docx')->deleteFileAfterSend(true);
    }

    public function printPDF($id, $type)
    {
        $letter = StudentOffer::findorfail($id);
        if ($type == 'html') {
            $filePath = $letter->path;
            if (file_exists($filePath)) {
                activityLog('Admin', 'Offer letter of ' . userName('Student', $letter->student_id) . ' printed');
                return Response::download($filePath);
            } else {
                abort(404, "File not found");
            }
        } else {
            $student_course_ids = $intake_course_ids = explode(',', $letter->intake_course_ids);
            $student_courses = StudentIntakeCourse::where('student_id', $letter->student_id)->whereIn('intake_course_id', $student_course_ids)->get();
            $student_fees = StudentIntakeCourseFee::where('student_id', $letter->student_id)->whereIn('intake_course_id', $student_course_ids)->get();
            $company = Company::first();
            // Instantiate and use the dompdf class 
            // $dompdf = new Dompdf(); 
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $dompdf = new Dompdf($options);
            // Load HTML content  
            $dompdf->loadHtml(view('student::offer-letter.show', compact('letter', 'company', 'student_courses', 'student_fees')));

            // (Optional) Setup the paper size and orientation  
            $dompdf->setPaper('A4', 'potrait');

            // Render the HTML as PDF  
            $dompdf->render();

            $file_name = userName('Student', $letter->student_id) . '_offer_letter.pdf';
            // Output the generated PDF (1 = download and 0 = preview) 
            $dompdf->stream($file_name, array("Attachment" => 1));
        }
    }
}
