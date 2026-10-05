<?php

namespace Modules\Setting\Http\Controllers;

use DateTime;
use League\Csv\Reader;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\Address\Entities\Address;
use Modules\Company\Entities\Company;
use Modules\Company\Entities\CompanyDeliverySite;
use Modules\Country\Entities\Country;
use Modules\Course\Entities\Course;
use Modules\Course\Entities\CourseDeliverySite;
use Modules\Course\Entities\CourseFee;
use Modules\Course\Entities\Unit;
use Modules\Intake\Entities\Intake;
use Modules\Intake\Entities\IntakeCourse;
use Modules\Intake\Entities\IntakeCourseFee;
use Modules\Intake\Entities\IntakeCourseFeeInstallment;
use Modules\Intake\Entities\IntakeUnit;
use Modules\Student\Entities\Student;
use Modules\Student\Entities\StudentAgent;
use Modules\Student\Entities\StudentDeliverySite;
use Modules\Student\Entities\StudentIntakeCourse;
use Modules\Student\Entities\StudentIntakeCourseCompetence;
use Modules\Student\Entities\StudentIntakeUnit;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:user');
    }

    /**
     * Display a listing of the RTO CSV.
     * @return Renderable
     */
    public function index()
    {
        if (checkRole('rto_import', 'add') == true) {
            activityLog('Admin', 'Opened Data Import Menu');
            return view('setting::dataimport.index');
        } else {
            return abort(404);
        }
    }

    public function rtoImportDeliverySite(Request $request)
    {
        $data = $request->all();
        $delivery_site = $data['delivery_site'];
        // Read and process dbo_Venue.csv
        $csvReader = Reader::createFromPath($delivery_site->getPathname(), 'r');
        $csvReader->setHeaderOffset(0);
        $sites = iterator_to_array($csvReader->getRecords());

        $company = Company::first();
        foreach ($sites as $key => $value) {
            if (isset($value['VenueCode'])) {
                $phone = getPhoneNumber($value['ContactNo']);
                $country_id = getCountryIdByName($value['Country']);
                $delivery_site = CompanyDeliverySite::create([
                    'company_id' =>  $company->id,
                    'site_name' => $value['VenueCode'],
                    'phone' => $phone,
                ]);
                Address::create([
                    'building_number' => $value['BuildingPropertyName'],
                    'flat_unit' => $value['FlatUnitDetails'],
                    'street_no' => $value['StreetNumber'],
                    'street_address' => $value['StreetName'],
                    'p_o_box' => '',
                    'suburb' => $value['Suburb'],
                    'state' => getStateCodeByInitial($value['State']),
                    'zip_code' => $value['Postcode'],
                    'country_id' => $country_id,
                    'type' => 'company_delivery_site',
                    'type_id' => $delivery_site->id,
                ]);
            } else {
                return redirect()->back()->with('failure', 'Please upload correct csv file for delivery site import');
            }
        }
        return redirect()->back()->with('success', 'Delivery Sites have been added to the database');
    }

    public function rtoImportCourse(Request $request)
    {
        $data = $request->all();
        $csvCourses = $data['courses'];
        // Read and process dbo_Venue.csv
        $csvCourseReader = Reader::createFromPath($csvCourses->getPathname(), 'r');
        $csvCourseReader->setHeaderOffset(0);
        $courses = iterator_to_array($csvCourseReader->getRecords());

        $csvSubjectCourses = $data['subject_courses'];
        // Read and process dbo_SubjectCourse.csv
        $csvSubjectCourseReader = Reader::createFromPath($csvSubjectCourses->getPathname(), 'r');
        $csvSubjectCourseReader->setHeaderOffset(0);
        $subjectCourses = iterator_to_array($csvSubjectCourseReader->getRecords());

        $csvUnits = $data['units'];
        // Read and process dbo_Unit.csv
        $csvUnitReader = Reader::createFromPath($csvUnits->getPathname(), 'r');
        $csvUnitReader->setHeaderOffset(0);
        $units = iterator_to_array($csvUnitReader->getRecords());

        foreach ($courses as $key => $value) {
            if (isset($value['CourseID'])) {
                foreach ($subjectCourses as $s_c) {
                    if (isset($s_c['SubjectID']) && isset($s_c['CourseID'])) {
                        foreach ($units as $u) {
                            if (isset($u['UnitID']) && isset($u['UnitName'])) {
                                $run = true;
                            } else {
                                return redirect()->back()->with('failure', 'Please upload correct csv file for unit import');
                            }
                        }
                    } else {
                        return redirect()->back()->with('failure', 'Please upload correct csv file for subject course import');
                    }
                }
                if ($run == true) {
                    $duration = intval($value['DurationMonth'] * 4.3524);
                    $hours = intval($value['WeeklyHours'] * $duration);
                    $course = Course::create([
                        'course_name' => $value['CourseName'],
                        'course_code' => $value['DisplayID'],
                        'cricos_code' => $value['CricosCode'],
                        'reference_name' => $value['CourseID'],
                        'delivery_mode' => 'YNN',
                        'internal' => 'Yes',
                        'predominant_delivery_mode',
                        'duration' => $duration,
                        'study_period' => 5,
                        'study_break' => 2,
                        'hours' => $hours,
                        'fee' => floatval(str_replace(',', '', $value['TuitionFee'])),
                        'fee_initial' => 0,
                        'fee_installment' => 0,
                        'onshore_fee' => floatval(str_replace(',', '', $value['DomesticCoursePrice'])),
                        'onshore_initial' => 0,
                        'onshore_installment' => 0,
                        'enrollment_fee' => 0,
                        'material_fee' => 0,
                        'total_units' => 5,
                    ]);
                    $course_id = $course->id;
                    $delivery_site['course_id'] = $course_id;
                    $delivery_site['company_delivery_site_id'] = 1;
                    CourseDeliverySite::create($delivery_site);
                    $offshore['course_id'] = $onshore['course_id'] = $course_id;
                    $offshore['name'] = 'General Offshore';
                    $onshore['name'] = 'General Onshore';
                    $offshore['enrollment_fee'] = $onshore['enrollment_fee'] = $course->enrollment_fee;
                    $offshore['material_fee'] = $onshore['material_fee'] = $course->material_fee;
                    $offshore['fee'] = $course->fee;
                    $onshore['fee'] = $course->onshore_fee;
                    $offshore['type'] = 'Offshore';
                    $onshore['type'] = 'Onshore';
                    $offshore['enrollment_fee'] = $course->fee_initial;
                    $offshore['installment'] = $course->fee_installment;
                    $onshore['initial_fee'] = $course->onshore_initial;
                    $onshore['installment'] = $course->onshore_installment;
                    // Create Onshore and Offshore Course Fee
                    CourseFee::create($offshore);
                    CourseFee::create($onshore);
                }
            } else {
                return redirect()->back()->with('failure', 'Please upload correct csv file for course import');
            }
        }

        foreach ($units as $unit) {
            foreach ($subjectCourses as $sc) {
                if ($sc['SubjectID'] == $unit['SubjectID']) {
                    $courseID = $sc['CourseID'];
                    $course = Course::where('reference_name', $courseID)->first();
                    $duration = $unit['ScheduledHours'] / 5;
                    Unit::create([
                        'code' => $unit['UnitID'],
                        'name' => $unit['UnitName'],
                        'type' => $unit['UnitType'],
                        'education_field' => $unit['AVM_FieldOfEdID'],
                        'hours' => $unit['ScheduledHours'],
                        'duration' => $duration,
                        'due_date' => 1,
                        'course_id' => $course->id,
                    ]);
                }
            }
        }
        return redirect()->back()->with('success', 'Courses and units have been added successfully');
    }

    public function rtoImportIntake(Request $request)
    {
        $data = $request->all();
        $intake = $data['intake'];
        // Read and process dbo_intake.csv
        $csvReader = Reader::createFromPath($intake->getPathname(), 'r');
        $csvReader->setHeaderOffset(0);
        $intakes = iterator_to_array($csvReader->getRecords());

        foreach ($intakes as $key => $value) {
            if (isset($value['IntakeName'])) {
                $checkIntake = Intake::where('name', $value['IntakeName'])->count();
                $date = DateTime::createFromFormat('d/m/Y', $value['IntakeDate']);
                if ($checkIntake == 0) {
                    Intake::create([
                        'name' => $value['IntakeName'],
                        'reference_name' => $value['IntakeName'],
                        'orientation_date' => $date->format('Y-m-d'),
                        'starting_date' => $date->format('Y-m-d'),
                    ]);
                }
            } else {
                return redirect()->back()->with('failure', 'Please upload correct csv file for intake import');
            }
        }

        foreach ($intakes as $dbo_intake) {
            $intake = Intake::where('name', $dbo_intake['IntakeName'])->first();
            $starting_date = DateTime::createFromFormat('d/m/Y', $dbo_intake['IntakeDate']);
            $endind_date = DateTime::createFromFormat('d/m/Y', $dbo_intake['FinishDate']);
            $course = Course::where('reference_name', $dbo_intake['CourseId'])->first();
            $intake_course = IntakeCourse::create([
                'intake_id' => $intake->id,
                'course_id' => $course->id,
                'reference_name' => $intake->reference_name,
                'starting_date' => $starting_date->format('Y-m-d'),
                'ending_date' => $endind_date->format('Y-m-d'),
                'duration' => $course->duration,
            ]);

            $units = $course->unit;
            foreach ($units as $unit) {
                IntakeUnit::create([
                    'intake_course_id' => $intake_course->id,
                    'unit_id' => $unit->id,
                    'starting_date' => $starting_date->format('Y-m-d'),
                    'ending_date' => $endind_date->format('Y-m-d'),
                    'due_date' => $endind_date->format('Y-m-d'),
                    'sequence' => 1,
                ]);
            }
            // Add Intake Course Fee
            $course_fees = CourseFee::where('course_id', $course->id)->get();
            if (count($course_fees) > 0) {
                foreach ($course_fees as $key => $value) {
                    $intake_course_fee = IntakeCourseFee::create([
                        'intake_course_id' => $intake_course->id,
                        'name' => $value->name,
                        'initial_fee' => $value->initial_fee,
                        'enrollment_fee' => $value->enrollment_fee,
                        'enrollment_fee_wavier' => $value->enrollment_fee_wavier,
                        'material_fee' => $value->material_fee,
                        'material_fee_wavier' => $value->material_fee_wavier,
                        'fee' => $value->fee,
                        'installment' => $value->installment,
                        'type' => $value->type,
                        'due_date' => $intake_course->ending_date,
                        'status' => $value->status
                    ]);
                    $total_amount = $course->enrollment_fee + $course->material_fee + $course->fee;
                    IntakeCourseFeeInstallment::create([
                        'intake_course_fee_id' => $intake_course_fee->id,
                        'name' => 'First Installment',
                        'enrollment_fee' => $course->enrollment_fee,
                        'material_fee' => $course->material_fee,
                        'amount' => $total_amount,
                        'due_date' => $intake_course->starting_date,
                    ]);
                }
            }
        }
        return redirect()->back()->with('success', 'Intakes, intake courses and intake units have been added successfully');
    }

    public function rtoImportStudent(Request $request)
    {
        $data = $request->all();
        $studentFile = $data['student'];
        $studentAddressDetailsFile = $data['student_address'];
        $studentSchoolingFile = $data['student_schooling'];
        $studentContactFile = $data['student_contact'];
        $studentServiceFile = $data['student_service'];
        $studentCourseFile = $data['student_course'];
        $studentResultFile = $data['student_result'];
        $studentResultUnitFile = $data['student_result_unit'];
        $studentCertificateFile = $data['student_certificate'];

        // Read and process dbo_Student.csv
        $csvReader = Reader::createFromPath($studentFile->getPathname(), 'r');
        $csvReader->setHeaderOffset(0);
        $students = iterator_to_array($csvReader->getRecords());

        // Read and process dbo_studentAddressDetails.csv
        $csvAddressReader = Reader::createFromPath($studentAddressDetailsFile->getPathname(), 'r');
        $csvAddressReader->setHeaderOffset(0);
        $studentsAddressDetails = iterator_to_array($csvAddressReader->getRecords());

        // Read and process dbo_student_schooling.csv
        $csvSchoolingReader = Reader::createFromPath($studentSchoolingFile->getPathname(), 'r');
        $csvSchoolingReader->setHeaderOffset(0);
        $studentsSchooling = iterator_to_array($csvSchoolingReader->getRecords());

        // Read and process dbo_studentcontact.csv
        $csvContactReader = Reader::createFromPath($studentContactFile->getPathname(), 'r');
        $csvContactReader->setHeaderOffset(0);
        $studentContacts = iterator_to_array($csvContactReader->getRecords());

        // Read and process dbo_studentServiceAndinfo.csv
        $csvServiceReader = Reader::createFromPath($studentServiceFile->getPathname(), 'r');
        $csvServiceReader->setHeaderOffset(0);
        $studentServices = iterator_to_array($csvServiceReader->getRecords());

        // Read and process dbo_studentcourse.csv
        $csvCourseReader = Reader::createFromPath($studentCourseFile->getPathname(), 'r');
        $csvCourseReader->setHeaderOffset(0);
        $studentCourses = iterator_to_array($csvCourseReader->getRecords());

        // Read and process dbo_studentresults.csv
        $csvResultReader = Reader::createFromPath($studentResultFile->getPathname(), 'r');
        $csvResultReader->setHeaderOffset(0);
        $studentResults = iterator_to_array($csvResultReader->getRecords());

        // Read and process dbo_studentResultsUnit.csv
        $csvResultUnitReader = Reader::createFromPath($studentResultUnitFile->getPathname(), 'r');
        $csvResultUnitReader->setHeaderOffset(0);
        $studentResultUnits = iterator_to_array($csvResultUnitReader->getRecords());

        // Read and process dbo_Certificate.csv
        $csvCertificateReader = Reader::createFromPath($studentCertificateFile->getPathname(), 'r');
        $csvCertificateReader->setHeaderOffset(0);
        $studentCertificates = iterator_to_array($csvCertificateReader->getRecords());

        foreach ($students as $key => $value) {
            foreach ($studentsAddressDetails as $address) {
                if ($address['StudentId'] == $value["StudentId"]) {
                    $building_no = $address['BuildingPropertyName'];
                    $flat_unit = $address['FlatUnitDetails'];
                    $suburb = $address['Suburb'];
                    $street_no = $address['StreetNumber'];
                    $street_address = $address['StreetName'];
                    $mobile = $address['Mobile'];
                    $post_code = $address['PostCode'];
                    $p_o_box = $address['PostalDeliveryBox'];
                    $country = $address['Country'];
                    $state = $address['State'];
                }
            }

            foreach ($studentsSchooling as $schooling) {
                if ($schooling['StudentId'] == $value['StudentId']) {
                    $school_level = $schooling['SchoolLevel'];
                    $school_type = $schooling['SchoolType'];
                    if ($schooling['AtSchoolFlag'] == "FALSE") $at_school = 'N';
                    else $at_school = 'Y';
                    if ($school_type == "null") $school_type = NULL;
                }
            }

            foreach ($studentContacts as $contact) {
                if ($contact['StudentID'] == $value['StudentId']) {
                    $emergency_relation = $contact['Relationship'];
                    $emergency_name = $contact['ContactName'];
                    $emergency_phone = $contact['Phone'];
                }
            }

            foreach ($studentServices as $service) {
                if ($service['StudentID'] == $value['StudentId']) {
                    if ($service['Disability'] == 'FALSE') $disability = 'N';
                    else $disability = 'Y';
                    $disability_identifier = $service['AreaOfDisable'];
                }
            }

            foreach ($studentResults as $result) {
                if ($result['StudentID'] == $value['StudentId']) {
                    if (strlen($result['StudyReason']) == 1) $study_reason = '0' . $result['StudyReason'];
                    else $study_reason = $result['StudyReason'];
                    $funding_source = $result['FundingSource'];
                }
            }

            foreach ($studentResultUnits as $resultUnit) {
                if ($resultUnit['StudentID'] == $value['StudentId']) {
                    $delivery_site = CompanyDeliverySite::where('site_name', $resultUnit['VenueCode'])->first();
                    if (!$delivery_site) {
                        $delivery_site = CompanyDeliverySite::find(1);
                    }
                }
            }

            if ($value['Title'] == 'Mr' || $value['Title'] == 'Ms') $value['Title'] = $value['Title'] . '.';
            $dob = DateTime::createFromFormat('d/m/Y h:i', $value['DoB']);
            $password = Hash::make($value['Email']);
            $student = Student::create([
                'salutation' => $value['Title'],
                'first_name' => $value['FirstName'],
                'family_name' => $value['LastName'],
                'date_of_birth' => $dob->format('Y-m-d'),
                'passport_no' => $value['PassportNo'],
                'phone' => $mobile,
                'mobile' => $mobile,
                'email' => $value['Email'],
                'password' => $password,
                'image' => 'themes/AdminLTE/dist/img/boxed-bg.png',
                'id_no' => $value['StudentId'],
                'country_id' => getAvetmissCountry($value['Nationality']),
                'overseas_address' => $value['KnowFrom'],
                'overseas_country_id' => getCountryFromNationality($value['Nationality']),
                'emergency_contact_person' => $emergency_name,
                'emergency_contact_number' => $emergency_phone,
                'emergency_contact_relation' => $emergency_relation,
                'school_based_flag' => $at_school,
                'school_level_identifier' => $school_level,
                'school_type_identifier' => $school_type,
                'indigenous_status_identifier' => $value['Abor_TorresStatus_Code'],
                'unique_student_identifier' => $value['USI'],
                'email_alternative' => $value['CollegeEmail'],
                'gender' => $value['Gender'],
                'disability_flag' => $disability,
                'disability_identifier' => $disability_identifier,
                'survey_contact_status' => $value['AVM_SurveyContactStatus'],
                'at_school' => $at_school,
                'labour_force_status_identifier' => $value['CurrentEmployStatus'],
                'citizenship_country' => getAvetmissCountry($value['Nationality']),
                'study_reason' => $study_reason,
                'funding_source_national' => $funding_source
            ]);
            Address::create([
                'building_number' => $building_no,
                'flat_unit' => $flat_unit,
                'street_no' => $street_no,
                'street_address' => $street_address,
                'p_o_box' => $p_o_box,
                'suburb' => $suburb,
                'state' => getStateCodeByInitial($state),
                'zip_code' => $post_code,
                'country_id' => getIdentifierDescription('COUNTRY IDENTIFIER', $country),
                'type' => 'student',
                'type_id' => $student->id,
            ]);
            StudentDeliverySite::create([
                'student_id' => $student->id,
                'company_delivery_site_id' => $delivery_site->id,
            ]);
            foreach ($studentCourses as $studentCourse) {
                if ($studentCourse['StudentID'] == $value['StudentId']) {
                    $course = Course::where('reference_name', $studentCourse['CourseID'])->first();
                    $starting_date = DateTime::createFromFormat('d/m/Y h:i', $studentCourse['StartDate']);
                    $ending_date = DateTime::createFromFormat('d/m/Y h:i', $studentCourse['FinishDate']);
                    $intake_course = IntakeCourse::where('course_id', $course->id)->where('starting_date', $starting_date->format('Y-m-d'))->first();
                    $student_intake_course = StudentIntakeCourse::create([
                        'student_id' => $student->id,
                        'intake_course_id' => $intake_course->id,
                        'starting_date' => $starting_date->format('Y-m-d'),
                        'ending_date' => $ending_date->format('Y-m-d'),
                        'duration' => $intake_course->duration,
                    ]);
                    foreach ($intake_course->intakeUnit as $unit) {
                        foreach ($studentResultUnits as $resultUnit) {
                            if ($value['StudentId'] == $resultUnit['StudentID'] && $unit->unit->code == $resultUnit['UnitID']) {
                                $funding_source_state_training_authority = $resultUnit['fundingsourceSTA'];
                                $unit_starting_date = DateTime::createFromFormat('d/m/Y h:iA', $resultUnit['UnitStartDate']);
                                if ($unit_starting_date == false) {
                                    $unit_starting_date = DateTime::createFromFormat('d/m/Y h:i', $resultUnit['UnitStartDate']);
                                }
                                $unit_ending_date = DateTime::createFromFormat('d/m/Y h:iA', $resultUnit['UnitFinishDate']);
                                if ($unit_ending_date == false) {
                                    $unit_ending_date = DateTime::createFromFormat('d/m/Y h:i', $resultUnit['UnitFinishDate']);
                                }
                                $today = date('Y-m-d');
                                $ed = $unit_ending_date->format('Y-m-d');
                                if ($today > $ed) $is_complete = 1;
                                else $is_complete = 0;
                                $delivery_mode = $resultUnit['AVM_DeliveryModes'];
                                $predominant_delivery_mode = $resultUnit['UnitDeliveryModeID'];
                                $commencing = $resultUnit['CommencingCourseID'];
                                if ($delivery_mode == 'YNN') {
                                    $internal = 'Yes';
                                }
                                if ($resultUnit['UnitCompetency'] == 'Enrolled') {
                                    $competency = NULL;
                                } elseif ($resultUnit['UnitCompetency'] == 'C') {
                                    $competency = 20;
                                } elseif ($resultUnit['UnitCompetency'] == 'NYC') {
                                    $competency = 30;
                                } elseif ($resultUnit['UnitCompetency'] == 'WD') {
                                    $competency = 40;
                                } elseif ($resultUnit['UnitCompetency'] == 'IC') {
                                    $competency = 41;
                                } elseif ($resultUnit['UnitCompetency'] == 'RPL') {
                                    $competency = 51;
                                } elseif ($resultUnit['UnitCompetency'] == 'RPL-N') {
                                    $competency = 52;
                                } elseif ($resultUnit['UnitCompetency'] == 'CT') {
                                    $competency = 60;
                                } elseif ($resultUnit['UnitCompetency'] == 'Superseded') {
                                    $competency = 61;
                                } elseif ($resultUnit['UnitCompetency'] == 'NS') {
                                    $competency = 66;
                                } elseif ($resultUnit['UnitCompetency'] == 'CE') {
                                    $competency = 70;
                                } elseif ($resultUnit['UnitCompetency'] == 'NA-C') {
                                    $competency = 81;
                                } elseif ($resultUnit['UnitCompetency'] == 'NA-NYC') {
                                    $competency = 82;
                                } elseif ($resultUnit['UnitCompetency'] == 'NYS') {
                                    $competency = 85;
                                } elseif ($resultUnit['UnitCompetency'] == 'R') {
                                    $competency = 90;
                                } else {
                                    $competency = NULL;
                                }
                            }
                        }

                        StudentIntakeUnit::create([
                            'student_intake_course_id' => $student_intake_course->id,
                            'intake_unit_id' => $unit->id,
                            'funding_source_national' => $funding_source,
                            'funding_source_state_training_authority' => $funding_source_state_training_authority,
                            'delivery_mode' => $delivery_mode,
                            'internal' => $internal,
                            'predominant_delivery_mode' => $predominant_delivery_mode,
                            'commencing' => $commencing,
                            'duration' => $unit->unit->duration,
                            'starting_date' => $unit_starting_date->format('Y-m-d'),
                            'ending_date' => $unit_ending_date->format('Y-m-d'),
                            'due_date' => $unit_ending_date->format('Y-m-d'),
                            'is_complete' => $is_complete,
                            'outcome' => $competency,
                        ]);
                    }
                }
            }
        }

        foreach ($studentCertificates as $certificate) {
            if (isset($certificate['CourseId'])) {
                $stc = Course::where('reference_name', $certificate['CourseId'])->first();
                if ($stc) {
                    $std = Student::where('id_no', $certificate['StudentID'])->first();
                    foreach ($std->intake as $intakes) {
                        $intake_course_id = $intakes->intakeCourse->course_id;
                        if ($stc->id == $intake_course_id) {
                            $std_ic = StudentIntakeCourse::where('student_id', $std->id)->where('intake_course_id', $intakes->intakeCourse->id)->first();
                            $award_status = 'N';
                            $certificate_type = $certificate['Type'];
                            $issued_date = DateTime::createFromFormat('d/m/Y h:i', $certificate['DateIssued']);
                            $parchment_issue_date = $issued_date->format('Y-m-d');
                            $parchment_no = $certificate['ManualCertificate'];
                            StudentIntakeCourseCompetence::create([
                                'student_intake_course_id' => $std_ic->id,
                                'award_status' => $award_status,
                                'certificate_type' => $certificate_type,
                                'parchment_issue_date' => $parchment_issue_date,
                                'parchment_no' => $parchment_no
                            ]);
                        }
                    }
                }
            }
        }
        return redirect()->back()->with('success', 'Students, student intake courses and student intake units have been added');
    }
}
