<?php

namespace Modules\Student\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CertificateTemplate\Entities\CertificateTemplate;
use Modules\Student\Entities\Student;
use Modules\Student\Entities\StudentIntakeCourse;
use Modules\Student\Entities\StudentIntakeCourseCompetence;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentIntakeCourseCompetenceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user');
    }

    /**
     * Display a listing of the student intake course competence.
     * @return Renderable
     */
    public function index($id)
    {
        $studentIntakeCourse = StudentIntakeCourse::findorfail($id);
        if (checkRole('student_intake_course', 'edit') == true) {
            activityLog('Admin', 'Opened ' . userName('Student', $studentIntakeCourse->student_id) . ' Intake Competence Page Opened');
            return view('student::intake.course.competence.index', compact('studentIntakeCourse'));
        } else {
            return redirect()->route('admin.student.intake.course.index', $studentIntakeCourse->student_id)->with('failure', 'This user does not have permission to edit student intake');
        }
    }

    /**
     * Store a newly created student intake course competence in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $id)
    {
        $studentIntakeCourse = StudentIntakeCourse::findorfail($id);
        if (checkRole('student_intake_course', 'edit') == true) {
            activityLog('Admin', 'Opened ' . userName('Student', $studentIntakeCourse->student_id) . ' Intake Competence Page Updated');
            $data = $request->all();
            $data['student_intake_course_id'] = $id;
            StudentIntakeCourseCompetence::create($data);
            return redirect()->back()->with('success', 'Competence has been updated');
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified student intake course competence in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $studentIntakeCourse = StudentIntakeCourse::findorfail($id);
        if (checkRole('student_intake_course', 'edit') == true) {
            activityLog('Admin', 'Opened ' . userName('Student', $studentIntakeCourse->student_id) . ' Intake Competence Page Updated');
            $data = $request->all();
            unset($data['_token'], $data['fee_id']);
            StudentIntakeCourseCompetence::where('student_intake_course_id', $id)->update($data);
            return redirect()->back()->with('success', 'Competence has been updated');
        } else {
            abort(404);
        }
    }
    
    public function generateCertificate(Request $request, $id)
    {
        $studentIntakeCourse = StudentIntakeCourse::findOrFail($id);
    
        if (checkRole('student_intake_course', 'edit') == true) {
            activityLog('Admin', 'Opened ' . userName('Student', $studentIntakeCourse->student_id) . ' Intake Competence certificate generate');
    
            $data = $request->all();
            $parchmentNumber = $studentIntakeCourse->studentIntakeCourseCompetence->parchment_no;
            $parchmentType = $studentIntakeCourse->studentIntakeCourseCompetence->certificate_type;
            $parchmentDate = dateFormat($request->parchment_issue_date);
            $studentID = $studentIntakeCourse->student->id_no;
            $studentName = userName('Student', $studentIntakeCourse->student_id);
            $courseCode = $studentIntakeCourse->intakeCourse->course->course_code;
            $courseName = $studentIntakeCourse->intakeCourse->course->course_name;
            $courseStartDate = dateFormat($studentIntakeCourse->intakeCourse->starting_date);
            $courseEndDate = dateFormat($studentIntakeCourse->intakeCourse->ending_date);
            $courseDuration = $studentIntakeCourse->intakeCourse->duration;
            $intakeUnit = $studentIntakeCourse->studentIntakeUnit;
    
            // Initialize variables
            $table_rows = [];
            $unit_tables = [];
            $table_counter = 1;
            $total_rows = count($intakeUnit);
            $remaining_rows = $total_rows;
    
            // Define dynamic row count rules
            $first_table_rows = 8;
            $subsequent_table_rows = 20;
            $last_table_rows = 8;
    
            // Start table header
            $table_header = "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 10px;'>
                <thead>
                    <tr>
                        <th style='padding: 5px; text-align:center'><strong>Unit Code</strong></th>
                        <th style='padding: 5px; text-align:center'><strong>Unit Title</strong></th>
                        <th style='padding: 5px; text-align:center'><strong>Results*</strong></th>
                    </tr>
                </thead>
                <tbody>";
    
            // Loop through each unit and assign rows accordingly
            foreach ($intakeUnit as $index => $key) {
                $unit_code_data = $key->intakeUnit->Unit->code;
                $unit_name_data = $key->intakeUnit->Unit->name;
                $unit_competency_data = getIdentifierValue('OUTCOME IDENTIFIER - NATIONAL', $key->outcome);
    
                // Determine the row limit for the current table
                if ($table_counter == 1) {
                    $current_row_limit = $first_table_rows;
                } elseif ($remaining_rows <= $last_table_rows) {
                    $current_row_limit = $last_table_rows;
                } else {
                    $current_row_limit = $subsequent_table_rows;
                }
    
                // Append row
                $table_rows[$table_counter][] = "<tr>
                        <td style='padding: 5px; text-align:center'>$unit_code_data</td>
                        <td style='padding: 5px; text-align:center'>$unit_name_data</td>
                        <td style='padding: 5px; text-align:center'>$unit_competency_data</td>
                    </tr>";
    
                // Check if we reached the row limit or it's the last row
                if (count($table_rows[$table_counter]) == $current_row_limit || $index + 1 == $total_rows) {
                    $table_body = implode("", $table_rows[$table_counter]);
    
                    // Store table
                    $unit_tables["unit_table$table_counter"] = $table_header . $table_body . "</tbody></table>";
    
                    // Move to the next table
                    $table_counter++;
                    $remaining_rows -= $current_row_limit;
                }
            }
    
            // Determine the total number of tables generated
            $totalTables = count($unit_tables);
    
            // Generate placeholders dynamically based on the number of tables
            $unitTablePlaceholders = [];
            for ($i = 1; $i <= $totalTables; $i++) {
                $unitTablePlaceholders["{{\$unit_table" . $i . "}}"] = $unit_tables["unit_table" . $i] ?? "";
            }
    
            // Replace placeholders with actual values
            $placeholders = array_merge([
                '{{$student_name}}' => e($studentName),
                '{{$student_id}}' => e($studentID),
                '{{$certificate_number}}' => $parchmentNumber,
                '{{$date_of_issue}}' => $parchmentDate,
                '{{$courseName}}' => $courseName,
                '{{$course_name}}' => $courseName,
                '{{$course_start_date}}' => $courseStartDate,
                '{{$course_end_date}}' => $courseEndDate,
                '{{$course_duration}}' => $courseDuration,
                '{{$course_code}}' => $courseCode,
            ], $unitTablePlaceholders);
    
            $certificateTemplate = CertificateTemplate::where('type', 'award')->first();
            if ($parchmentType == "Award") {
                $certificateTemplate = CertificateTemplate::where('type', 'award')->first();
            } else {
                $certificateTemplate = CertificateTemplate::where('type', 'soa')->first();
            }
    
            $templateName = $certificateTemplate->name;
            $filePath = public_path($certificateTemplate->path);
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
            $newTemplateName = $templateName . "-" . $studentName;
            $pathToSave = "templates/certificate/students/" . $newTemplateName . ".html";
            $savedFilePath = public_path($pathToSave);
    
            if (!File::exists(public_path('templates/certificate/students/'))) {
                File::makeDirectory(public_path('templates/certificate/students/'), 0755, true, true);
            }
            file_put_contents($savedFilePath, $htmlContentNew);
    
            // Generate PDF using Dompdf
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($htmlContentNew)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isPhpEnabled' => false,
                    'defaultFont' => 'sans-serif',
                    'isRemoteEnabled' => true,
                    'dpi' => 120
                ]);
    
            return $pdf->download("$newTemplateName.pdf");
        } else {
            abort(404);
        }
    }
    
    


            // // **Generate PDF with DomPDF**
            // $dompdf = new Dompdf();
            // $dompdf->loadHtml($htmlContentNew);
            // $dompdf->setPaper('a4', 'portrait');
            // $dompdf->render();
            // $pdf = $dompdf->output();
            
            // // Return PDF to frontend for automatic printing
            // return response()->streamDownload(function () use ($pdf) {
            //     echo $pdf;
            // }, "$templateName-$studentName.pdf");
}
