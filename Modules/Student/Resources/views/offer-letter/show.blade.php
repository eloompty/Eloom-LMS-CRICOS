@php
    $orgName = $company->company_name;
    $orgShortName = $company->trading_name ?: $company->company_name;
    $orgAddress = $company->address
        ? collect([$company->address->building_number, $company->address->flat_unit, trim($company->address->street_no . ' ' . $company->address->street_address), $company->address->suburb, trim($company->address->state . ' ' . $company->address->zip_code)])->filter()->implode(', ')
        : '';
    $orgWebsite = config('app.url');
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Student Offer</title>
    <style>
        /* Styles go here */

        .page-header,
        .page-header-space {
            height: 100px;
        }

        .page-footer,
        .page-footer-space {
            height: 50px;

        }

        .page-footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            /* border-top: 1px solid black; */
            /* for demo */
            /* background: yellow; */
            /* for demo */
        }

        .page-header {
            position: fixed;
            top: 0mm;
            width: 100%;
            /* border-bottom: 1px solid black; */
            /* for demo */
            /* background: yellow; */
            /* for demo */
        }

        .page {
            page-break-after: always;
        }

        .center {
            display: block;
            margin-left: auto;
            margin-right: auto;
            width: 50%;
        }

        .smallf {
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
        }

        .smallff {
            font-family: "Times New Roman", Times, serif;
            font-size: 10px;
        }

        hr.thin {
            height: 1px;
            border: 0;
            color: #333;
            background-color: #333;
            width: 80%;
        }

        .tdbg {
            background: #d9d9d9;
        }

        @page {
            margin: 20mm, 20mm;
        }

        @media print {
            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            button {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="page-header" style="text-align: center">
        <table width="700px;">
            <tr>
                <td align="right">
                    <img src="{{ asset(getSettingValue('offer_college_logo')) }}" height="80" alt=""></img>
                </td>
            </tr>
        </table>
        <br />
        <button type="button" onClick="window.print()" style="background: pink">
            PRINT!
        </button>
    </div>

    <div class="page-footer">
        <table width="700px">
            <tr>
                <td class="smallff" colspan="2" align="center">
                    {{ $company->company_name }}, RTO Code {{ $company->rto_no }} CRICOS Provider Code {{ $company->cricos_no }}<br>
                    {{ $orgAddress }}<br>
                    Email: {{ $company->email }} Website: {{ $orgWebsite }}<br><br><br><br>
                </td>
            </tr>
        </table>
    </div>

    <table>

        <thead>
            <tr>
                <td>
                    <!--place holder for the fixed-position header-->
                    <div class="page-header-space"></div>
                </td>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>
                    <!--*** CONTENT GOES HERE ***-->
                    <div class="page">
                        <table width="700px;">
                            <tr>
                                <td class="smallf" colspan="2">
                                    <b>INTERNATIONAL STUDENT LETTER OF OFFER & AGREEMENT</b><br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="smallf" colspan="2">
                                    <p>Date: @if ($letter->issue_date == NULL) {{ dateFormat($letter->created_at) }} @else {{ dateFormat($letter->issue_date) }} @endif</p><br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="smallf" colspan="2">
                                    {{ userName('Student', $letter->student_id) }}<br>
                                </td>
                            </tr>
                            <tr>
                                <td class="smallf" colspan="2">
                                    {{ $letter->student->overseas_address }} {{ $letter->student->overseasCountry->name }}<br>
                                </td>
                            </tr>
                            <tr>
                                <td class="smallf" colspan="2"> Offer NUMBER: &nbsp; {{ offerNumber($letter->id) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="smallf" colspan="2">
                                    <br>
                                    <p>Dear {{ $letter->student->first_name }},<br><br>

                                        Thank you for your application to enrol with {{ $orgName }} ({{ $orgShortName }}). We have reviewed your application and take great pleasure in offering you a place in the course you have applied for. </p>
                                </td>
                            </tr>
                        </table>
                        <table cellspacing="0" cellpadding="1" border="1" width="700px">
                            <tr>
                                <td class="smallf tdbg" width="50" height="30"> &nbsp;<b>Course Code</b></td>
                                <td class="smallf tdbg" width="150"> &nbsp;<b>Course</b></td>
                                <td class="smallf tdbg" width="45"> &nbsp;<b>CRICOS Course Code</b></td>
                                <td class="smallf tdbg" width="50"> &nbsp;<b>Start Date</b></td>
                                <td class="smallf tdbg" width="50"> &nbsp;<b>Finish Date</b></td>
                                <td class="smallf tdbg" width="60"> &nbsp;<b>Duration</b></td>
                            </tr>
                            @foreach($student_courses as $course)
                            <tr>
                                <td class="smallf" width="50" height="25">{{ $course->intakeCourse->course->course_code }}</td>
                                <td class="smallf" width="150"> {{ $course->intakeCourse->course->course_name }}</td>
                                <td class="smallf" width="45"> {{ $course->intakeCourse->course->cricos_code }}</td>
                                <td class="smallf" width="50"> {{ dateFormat($course->intakeCourse->starting_date) }}</td>
                                <td class="smallf" width="50"> {{ dateFormat($course->intakeCourse->ending_date) }}</td>
                                <td class="smallf" width="60"> {{ $course->intakeCourse->duration }} Weeks</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td class="smallf" colspan="6">{{ $letter->credit_description }}</td>
                            </tr>

                        </table>
                        <div style="clear:both;"></div>
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td class="smallf">
                                    You will find the details of your enrolment along with the terms and conditions attached. If you would like to take up the offer of this place, you should do so immediately. To secure your place in the course, please complete the Written Agreement included at the end of this document to indicate your acceptance and send it back within {{ dateformat($letter->expiry_date) }}.
                                    <br><br>

                                    Please pay attention to Course Fee Overview, Payment Plan and Schedule of Charges attached. Please also pay attention to document requirements in Conditions to be fulfilled section, if any. If you require further advice or clarification regarding the attached documents, please contact us at our office.
                                    <br> <br>
                                    The Orientation date is {{ dateformat($student_courses[0]->intakeCourse->intake->orientation_date) }}. The class start date is {{ dateformat($student_courses[0]->intakeCourse->starting_date) }}. Be present in the premise by 9.00 am. <br><br>
                                    Your classes will be held in the following address: @if ($student_courses[0]->intakeCourse->course->deliverSite != NULL) {{ $student_courses[0]->intakeCourse->course->deliverSite->companyDeliverySite->site_name }} @else - @endif<br>
                                    We look forward to welcoming you to {{ $orgName }} and wish you all the best with your studies.
                                    <br><br>
                                    Kind regards,<br><br><br>
                                    <img src="{{ asset(getSettingValue('offer_signature')) }}" height="25" alt=""></img>
                                    <br>
                                    {{ getSettingValue('offer_signed_by_name') }}<br>
                                    {{ getSettingValue('offer_signed_by_designation') }}<br>
                                    {{ $company->company_name }} <br><br>

                                    {{ $company->company_name }}, RTO Code {{ $company->rto_no }} CRICOS Provider Code {{ $company->cricos_no }}<br>
                    {{ $orgAddress }}<br>
                    Email: {{ $company->email }} Website: {{ $orgWebsite }}<br><br><br>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="page">
                        <div style="clear:both;"></div>
                        <p class="smallf"><b>Course Fees Overview</b></p>
                        <table cellspacing="0" cellpadding="1" border="1" width="700px">
                            @foreach($student_fees as $index => $fee)
                            <tr>
                                <td class="smallf tdbg" width="50%"> &nbsp;Enrolment Application Fee (non-refundable)</b></td>
                                <td class="smallff">
                                    $ {{ $fee->enrollment_fee }}
                                </td>
                            </tr>
                            <tr>
                                <td class="smallf tdbg" height="18"> &nbsp;Tuition Fee {{ $fee->intakeCourse->course->course_name }} </td>
                                <td class="smallff">$ {{ $fee->fee }}</td>
                            </tr>
                            <tr>
                                <td class="smallf tdbg" height="18"> &nbsp;Non-Tuition Fee: Materials Fee ({{ $fee->intakeCourse->course->course_name }}) </td>
                                <td class="smallff">$ {{ $fee->material_fee }}</td>
                            </tr>
                            <tr>
                                <td class="smallf tdbg" height="18"> &nbsp;Total Course Fees </td>
                                <td class="smallff">$ {{ $fee->enrollment_fee + $fee->fee + $fee->material_fee }}</td>
                            </tr>
                            <?php if (!empty($offerdata['oshc_fee'])) { ?>
                                <tr>
                                    <td class="smallf tdbg" height="18"> &nbsp;Overseas Student Health Cover </td>
                                    <td class="smallff">$ <?php echo '$' . $offerdata['oshc_fee']; ?></td>
                                </tr>
                            <?php } ?>

                            @php
                            $enrollment[] = $fee->enrollment_fee;
                            $totatFee[] = $fee->fee;
                            $materialFee[] = $fee->material_fee;
                            $first_instalments[] = $fee->installments->first()->amount;
                            $total_first_installments[] = $fee->installments->first()->studentIntakeCourseFee->intakeCourse->course->course_name . ': $'. $fee->installments->first()->amount;
                            $installments[] = $fee->installments->where('name', '!=', 'First Installment');
                            $cricos[] = $fee->installments->first()->studentIntakeCourseFee->intakeCourse->course->cricos_code;
                            @endphp
                            @endforeach
                            @php
                            $total_enrollment = array_sum($enrollment);
                            $total_fee = array_sum($totatFee);
                            $total_material_fee = array_sum($materialFee);
                            $first_installment = array_sum($first_instalments);
                            @endphp
                        </table>
                        <p class="smallf">Proforma Student Invoice</p>
                        <div id="center">
                            <!-- here -->

                            <table cellspacing="0" cellpadding="0" border="1">
                                <tr>
                                    <td class="smallf tdbg" width="90" height="18"> &nbsp;<b>Enrollment Fee</b></td>
                                    <td class="smallf tdbg" width="100"> &nbsp;<b>Tuition Fee</b></td>
                                    <td class="smallf tdbg" width="65"> &nbsp;<b>OSHC</b></td>
                                    <td class="smallf tdbg" width="100"> &nbsp;<b>Others (Student <br>ID, Material Fee)</b></td>
                                    <td class="smallf tdbg" width="80"> &nbsp;<b>Discounts</b></td>
                                    <td class="smallf tdbg" width="90"> &nbsp;<b>Total Summary</b></td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="90" height="16"> &nbsp;${{ $total_enrollment }}</td>
                                    <td class="smallf" width="100"> &nbsp;${{ $total_fee }}</td>
                                    <td class="smallf" width="65"> &nbsp;</td>
                                    <td class="smallf" width="100"> &nbsp;${{ $total_material_fee }}</b></td>
                                    <td class="smallf" width="80"> &nbsp;$0.00</td>
                                    <td class="smallf" width="90"> &nbsp;${{ $total_enrollment + $total_fee + $total_material_fee }}</td>
                                </tr>
                            </table>
                        </div><br>
                        <div id="center">
                            <br>
                            <table cellspacing="0" cellpadding="0" border="1">
                                <tr>
                                    <td class="smallf tdbg" width="50" height="18"> &nbsp;<b>Payment No.</b></td>
                                    <td class="smallf tdbg" width="150"> &nbsp;<b>Due Date</b></td>
                                    <td class="smallf tdbg" width="100"> &nbsp;<b>Tuition Fees</b></td>
                                    <td class="smallf tdbg" width="100"> &nbsp;<b>Non-Tuition Fees</b></td>
                                    <td class="smallf tdbg" width="100"> &nbsp;<b>Total Amount</b></td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50" height="16"> &nbsp;1</td>
                                    <td class="smallf" width="80"> &nbsp;Due on acceptance of offer

                                    </td>
                                    <td class="smallf" width="100">
                                        Total: $ {{ $first_installment }};
                                        @foreach ($total_first_installments as $fi)
                                        {{ $fi }}
                                        @endforeach
                                    </td>
                                    <td class="smallf" width="125"> &nbsp;${{ $total_enrollment + $total_material_fee }}
                                    <td class="smallf" width="90"> &nbsp;{{ $total_enrollment + $total_fee + $total_material_fee }}
                                </tr>
                            </table>
                            <br>
                            <table cellspacing="0" cellpadding="0" border="1">
                                <tr>
                                    <td class="smallf tdbg" colspan="7"><b>Payment Schedule</b></td>
                                </tr>
                                <tr>
                                    <td class="smallf tdbg" width="30" height="18"> &nbsp;<b>Payment No.</b></td>
                                    <td class="smallf tdbg" width="135" height="18"> &nbsp;<b>Course</b></td>
                                    <td class="smallf tdbg" width="80"> &nbsp;<b>Due Date</b></td>
                                    <td class="smallf tdbg" width="60"> &nbsp;<b>Tuition Fees</b></td>
                                    <td class="smallf tdbg" width="65"> &nbsp;<b>Non-Tuition Fees</b></td>
                                    <td class="smallf tdbg" width="30"> &nbsp;<b>Discounts</b></td>
                                    <td class="smallf tdbg" width="80"> &nbsp;<b>Total</b></td>
                                </tr>
                                @php $index = 2 @endphp
                                @foreach($installments as $key => $installment)
                                @foreach($installment as $number => $value)
                                <tr>
                                    <td class="smallf" width="30" height="16"> &nbsp;{{ $index }}</td>
                                    <td class="smallf" width="135" height="16"> &nbsp;{{ $value->studentIntakeCourseFee->intakeCourse->course->course_code }} {{ $value->studentIntakeCourseFee->intakeCourse->course->course_name }}</td>
                                    <td class="smallf" width="80"> &nbsp;{{ dateFormat($value->due_date) }}</td>
                                    <td class="smallf" width="60"> &nbsp;${{ $value->amount }}</td>
                                    <td class="smallf" width="65"> &nbsp;$0</b></td>
                                    <td class="smallf" width="30"> &nbsp;$0</td>
                                    <td class="smallf" width="80"> &nbsp;${{ $value->amount }}</td>
                                </tr>
                                @php $index++ @endphp
                                @endforeach
                                @endforeach

                                <tr>
                                    <td colspan="7">&nbsp;</td>
                                </tr>
                            </table>


                        </div>
                    </div>
                    <div class="page">
                        <div>
                            <table width="700px;">

                                <tr>
                                    <td class="smallf"><b>Payment Details</b><br><br>
                                        I am paying Fees to confirm enrolment by the method indicated below: (please tick one box)<br>

                                        <input id="click1" name="click1" type="checkbox" />Bank Cheque or Bank Draft (attached) payable to {{ $orgName }}<br>
                                        <input id="click2" name="click2" type="checkbox" /> Bank Transfer (a copy of the bank receipt is attached)<br><br>
                                        Please contact {{ $company->email }} for bank transfer details.
                                        <br>
                                        <br>

                                        Important:<br>

                                        In the payment message or instruction, please indicate clearly your full name, so we can identify your payment easily.<br><br>

                                        <input id="click3" name="click3" type="checkbox" /> <label for="click3"> <b>Credit Card</b><br>
                                            The undersigned authorises {{ $orgShortName }} to debit the credit card as indicated below:<br>


                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf"><br><br>Credit Card Type: Visa Master Card <br>
                                        Credit Card No.<br> CCV No*: <br>
                                        Expiry Date: <br>
                                        Name on Card: <br>
                                        Cardholders Signature: <br><br>


                                        *This is the three digit number printed near the signature panel on the back of the card
                                        <br>
                                    </td>
                                </tr>


                            </table>
                            <table width="700px" class="smallff">
                                <tr>
                                    <td class="smallf">
                                        I will be studying at {{ $orgShortName }} on a:<br>
                                        Student Visa. I will apply for my visa at the Australian Diplomatic Mission or DIBP office located in<br><br><br>

                                        __________________________[insert the location, address and hours open]<br><br><br>

                                        Nationality:_______________________________ Passport No:__________________________<br><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallff">
                                        <b>Declaration.</b><br>

                                        I understand this Acceptance constitutes a written agreement with {{ $orgShortName }}. I have read and understood the terms and Conditions of Enrolment as detailed in the Acceptance Agreement, and I agree to abide by them. I declare that all information I have provided is true and correct and I am now paying the fees to confirm enrolment.
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" colspan="2" height="30"><br><br><br><br><br>

                                        _____________________________________ &nbsp;&nbsp;&nbsp;&nbsp;________________________________<br><br>

                                        Signature of Student* &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;
                                        Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/<br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallff">
                                        Student's signature must be the same as on their passport (please supply a copy)<br>
                                        <br>

                                        Please return your signed course Acceptance Agreement and completed Payment Details page with evidence of your payment to the Admissions Coordinator email at {{ $company->email }} or to a relevant campus at the address shown below:<br><br>

                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="page">
                        <p>&nbsp;</p>
                        <p class="smallf"><b>Letter of Offer / Written Agreement</b></p>
                        <p class="smallf">The details of your offer are as stated in the table below. This document ensures your Consumer rights are protected under Australian law. <br>Please check that these are correct and contact the person referred to in the cover letter of this offer if any changes are required.</p>
                        <div id="center">
                            <table cellspacing="0" cellpadding="1" border="1" width="700px">
                                <tr>
                                    <td class="smallf" width="50%"> &nbsp;RTO Legal Name:</td>
                                    <td class="smallff">
                                        &nbsp;<b>{{ $orgName }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%"> &nbsp;RTO Name:</td>
                                    <td class="smallff">
                                        &nbsp;<b>{{ $orgName }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%"> &nbsp;RTO Number:</td>
                                    <td class="smallff">
                                        &nbsp;{{ $company->rto_no }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%"> &nbsp;CRICOS Provider Number:</td>
                                    <td class="smallff">
                                        &nbsp;{{ $company->cricos_no }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%"> &nbsp;CRICOS Course CODE:</td>
                                    <td class="smallff">
                                        &nbsp;{{ implode(",", $cricos) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%"> &nbsp;Student Name:</td>
                                    <td class="smallff">
                                        &nbsp;{{ userName('Student', $letter->student_id) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%"> &nbsp;Passport Number: </td>
                                    <td class="smallff">
                                        &nbsp;{{ $letter->student->passport_no }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%"> &nbsp;Offer Number:</td>
                                    <td class="smallff">
                                        &nbsp;{{ $letter->id }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%"> &nbsp;Address:</td>
                                    <td class="smallff">
                                        &nbsp;@if ($student_fees->first()->type == 'Onshore')
                                        {{ fullAddress('Student', $letter->student_id) }}
                                        @else
                                        echo {{ $letter->student->overseas_address }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%"> &nbsp;Date of Birth:</td>
                                    <td class="smallff">
                                        &nbsp;{{ dateFormat($letter->student->date_of_birth) }}
                                    </td>
                                </tr>
                            </table>
                        </div><br>
                        <div id="center">
                            <table cellspacing="0" cellpadding="1" border="1" width="700px">
                                <tr>
                                    <td class="smallf tdbg" width="50" height="30"> &nbsp;<b>Course Code</b></td>
                                    <td class="smallf tdbg" width="150"> &nbsp;<b>Course</b></td>
                                    <td class="smallf tdbg" width="45"> &nbsp;<b>CRICOS Course Code</b></td>
                                    <td class="smallf tdbg" width="50"> &nbsp;<b>Start Date</b></td>
                                    <td class="smallf tdbg" width="50"> &nbsp;<b>Finish Date</b></td>
                                    <td class="smallf tdbg" width="60"> &nbsp;<b>Duration</b></td>
                                </tr>
                                @foreach($letter->student->intake as $intake_course)
                                <tr>
                                    <td class="smallf" width="50" height="25"> &nbsp;{{ $intake_course->intakeCourse->course->course_code }}</td>
                                    <td class="smallf" width="150"> &nbsp;{{ $intake_course->intakeCourse->course->course_name }}</td>
                                    <td class="smallf" width="45"> &nbsp;{{ $intake_course->intakeCourse->course->cricos_code }}</td>
                                    <td class="smallf" width="50"> &nbsp;{{ dateFormat($intake_course->intakecourse->starting_date) }}</td>
                                    <td class="smallf" width="50"> &nbsp;{{ dateFormat($intake_course->intakecourse->ending_date) }}</td>
                                    <td class="smallf" width="60"> &nbsp;{{ $intake_course->intakecourse->duration }} Weeks</td>
                                </tr>
                                @endforeach
                            </table>
                        </div><br>
                        <div id="center">
                            <table cellspacing="0" cellpadding="1" border="1" width="700px">
                                <tr>
                                    <td class="smallf" width="50%" height="20"> &nbsp;Orientation Date:</b></td>
                                    <td class="smallff" colspan="2">
                                        &nbsp;{{ dateFormat($student_courses[0]->intakecourse->intake->orientation_date) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%" height="20"> &nbsp;Location:</b></td>
                                    <td class="smallff" colspan="2">
                                        &nbsp;@if ($student_courses[0]->intakeCourse->course->deliverSite != NULL) {{ $student_courses[0]->intakeCourse->course->deliverSite->companyDeliverySite->site_name }} @else - @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50%" height="20"> &nbsp;Mode of Study :</b></td>
                                    <td class="smallff">
                                        &nbsp;Face to Face
                                    </td>
                                    <td class="smallff">
                                        &nbsp;<!-- No Online, work-placement or community/research- based training -->
                                    </td>
                                </tr>
                                <!-- <tr>
                                    <td class="smallf" width="50%" height="25"> &nbsp;Recognition of Prior Learning application: </b><br>Yes / No</td>
                                    <td class="smallff">
                                        &nbsp;Credit application: <br>Yes / No
                                    </td>
                                    <td class="smallff">
                                        &nbsp;
                                    </td>
                                </tr> -->
                            </table>
                        </div>
                        @if ($letter->student->studentAgent != NULL)
                        <p class="smallf"><b>Agent Details</b></p>
                        <div id="center">
                            <table cellspacing="0" cellpadding="1" border="1" width="700px">
                                <tr>
                                    <td class="smallf" width="50%" height="30"> &nbsp;Agent Name:</b><br>{{ $letter->student->studentAgent->agent->company_name }}</td>
                                    <td class="smallff">
                                        &nbsp;Address: {{ $letter->student->studentAgent->agent->address }} {{ $letter->student->studentAgent->agent->city }}<br>
                                        &nbsp;Phone: {{ $letter->student->studentAgent->agent->mobile }} {{ $letter->student->studentAgent->agent->office_phone }}<br>
                                        &nbsp;Website: {{ $letter->student->studentAgent->agent->url }}<br>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        @endif
                        <p class="smallf"><b>Conditions to be Fulfilled</b></p>
                        <div id="center">
                            <table cellspacing="0" cellpadding="1" border="1" width="700px">
                                <tr>
                                    <td class="smallf" height="40">{{ $letter->condition_description }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="page">
                        <div id="pg1">
                            <table width="700px;">
                                <!-- <tr>
                                    <td class="smallf" colspan="2">
                                        Letter of Offer / Student Agreement
                                    </td>
                                </tr> -->
                                <tr>
                                    <td class="smallf" colspan="2">
                                        <p>{{ dateFormat($letter->created_at) }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="125">
                                        <b>Student Name:</b>
                                    </td>
                                    <td class="smallf">
                                        {{ userName('Student', $letter->student_id) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="125">
                                        <b>Applicant ID:</b>
                                    </td>
                                    <td class="smallf">
                                        {{ $letter->id }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="125">
                                        <b>Date of Birth:</b>
                                    </td>
                                    <td class="smallf">
                                        {{ dateFormat($letter->student->date_of_birth) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" colspan="2" align="center"><br><b>Proforma Student Invoice</b></td>
                                </tr>
                            </table>
                            <table cellspacing="0" cellpadding="0" border="1">
                                <tr>
                                    <td class="smallf tdbg" width="90" height="18"> &nbsp;<b>Enrollment Fee</b></td>
                                    <td class="smallf tdbg" width="100"> &nbsp;<b>Tuition Fee</b></td>
                                    <td class="smallf tdbg" width="65"> &nbsp;<b>OSHC</b></td>
                                    <td class="smallf tdbg" width="100"> &nbsp;<b>Others (Student <br>ID, Material Fee)</b></td>
                                    <td class="smallf tdbg" width="80"> &nbsp;<b>Discounts</b></td>
                                    <td class="smallf tdbg" width="90"> &nbsp;<b>Total Summary</b></td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="90" height="16"> &nbsp;${{ $total_enrollment }}</td>
                                    <td class="smallf" width="100"> &nbsp;${{ $total_fee }}</td>
                                    <td class="smallf" width="65"> &nbsp;</td>
                                    <td class="smallf" width="100"> &nbsp;${{ $total_material_fee }}</b></td>
                                    <td class="smallf" width="80"> &nbsp;$0.00</td>
                                    <td class="smallf" width="90"> &nbsp;${{ $total_enrollment + $total_fee + $total_material_fee }}</td>
                                </tr>
                            </table>
                            <br>
                            <table cellspacing="0" cellpadding="0" border="1">
                                <tr>
                                    <td class="smallf tdbg" width="50" height="18"> &nbsp;<b>Payment No.</b></td>
                                    <td class="smallf tdbg" width="150"> &nbsp;<b>Due Date</b></td>
                                    <td class="smallf tdbg" width="100"> &nbsp;<b>Tuition Fees</b></td>
                                    <td class="smallf tdbg" width="100"> &nbsp;<b>Non-Tuition Fees</b></td>
                                    <td class="smallf tdbg" width="100"> &nbsp;<b>Total Amount</b></td>
                                </tr>
                                <tr>
                                    <td class="smallf" width="50" height="16"> &nbsp;1</td>
                                    <td class="smallf" width="80"> &nbsp;Due on acceptance of offer

                                    </td>
                                    <td class="smallf" width="100">
                                        Total: $ {{ $first_installment }};
                                        @foreach ($total_first_installments as $fi)
                                        {{ $fi }}
                                        @endforeach
                                    </td>
                                    <td class="smallf" width="125"> &nbsp;${{ $total_enrollment + $total_material_fee }}
                                    <td class="smallf" width="90"> &nbsp;{{ $total_enrollment + $total_fee + $total_material_fee }}
                                </tr>
                            </table>
                            <br>
                            <table cellspacing="0" cellpadding="0" border="1">
                                <tr>
                                    <td class="smallf tdbg" colspan="7"><b>Payment Schedule</b></td>
                                </tr>
                                <tr>
                                    <td class="smallf tdbg" width="30" height="18"> &nbsp;<b>Payment No.</b></td>
                                    <td class="smallf tdbg" width="135" height="18"> &nbsp;<b>Course</b></td>
                                    <td class="smallf tdbg" width="80"> &nbsp;<b>Due Date</b></td>
                                    <td class="smallf tdbg" width="60"> &nbsp;<b>Tuition Fees</b></td>
                                    <td class="smallf tdbg" width="65"> &nbsp;<b>Non-Tuition Fees</b></td>
                                    <td class="smallf tdbg" width="30"> &nbsp;<b>Discounts</b></td>
                                    <td class="smallf tdbg" width="80"> &nbsp;<b>Total</b></td>
                                </tr>
                                @foreach($installments as $key => $installment)
                                @foreach($installment as $number => $value)
                                <tr>
                                    <td class="smallf" width="30" height="16"> &nbsp;{{ $number+1 }}</td>
                                    <td class="smallf" width="135" height="16"> &nbsp;{{ $value->studentIntakeCourseFee->intakeCourse->course->course_code }} {{ $value->studentIntakeCourseFee->intakeCourse->course->course_name }}</td>
                                    <td class="smallf" width="80"> &nbsp;{{ dateFormat($value->due_date) }}</td>
                                    <td class="smallf" width="60"> &nbsp;${{ $value->amount }}</td>
                                    <td class="smallf" width="65"> &nbsp;$0</b></td>
                                    <td class="smallf" width="30"> &nbsp;$0</td>
                                    <td class="smallf" width="80"> &nbsp;${{ $value->amount }}</td>
                                </tr>
                                @endforeach
                                @endforeach

                                <tr>
                                    <td colspan="7">&nbsp;</td>
                                </tr>
                            </table>

                        </div>
                    </div>
                    <div class="page">
                        <div id="pg1">
                            <table width="700px;" cellspacing="5px">
                                <tr>
                                    <td class="smallf" width="50%">
                                        <p>
                                            <b>Terms and Conditions of Enrolment</b><br><br>

                                            This document ensures your consumer rights are protected under Australian law. Please follow these instructions.<br><br>

                                            <li> Read through the following pages to ensure you understand the expectations upon you and what you are agreeing to.<br>
                                            <li> Sign the Student Agreement section and return it to us.<br>
                                            <li> Where conditions are listed, provide evidence that you have met these conditions;<br>
                                            <li> Include your enrolment deposit/payment details.<br><br>

                                                Before signing this agreement, it is important you understand:<br>
                                            <li> What you are agreeing to<br>
                                            <li> Our Fees and Refunds policy<br>
                                            <li> Our policies and procedures as outlined in the Student Handbook<br>
                                            <li> Your responsibilities as a student<br>
                                            <li> Our responsibilities as the RTO/CRICOS provider.<br><br>

                                                Therefore, we have summarised these matters for you below. Please refer to the International Student Handbook, which is available in our website {{ $orgWebsite }}, for further information. The International Student Handbook also contains important information extracted from our Complaints and Appeals, Fees and Refunds, and Deferral, Suspension and Cancellation policies and procedures.<br><br>

                                                <b>Student Code of Conduct</b><br><br>

                                                <b>Student Rights</b><br><br>

                                                All students have the right to:<br>

                                            <li> Be treated fairly and with respect by all students and staff.<br>
                                            <li> Learn in a supportive environment, which is free from harassment, discrimination and victimisation.<br>
                                            <li> Learn in a healthy and safe environment where the risks to personal health and safety are minimised.<br>

                                        </p>
                                    </td>
                                    <td class="smallf">
                                        <p>

                                            <li> Have their personal details and records kept private and secure according to our Privacy Policy.<br>
                                            <li> Access the information {{ $orgShortName }} holds about them.<br>
                                            <li> Have their complaints and appeals dealt with fairly, promptly, confidentially and without retribution.<br>
                                            <li> Make appeals about procedural and assessment decisions.<br>
                                            <li> Receive training, assessment and support services that meet their individual needs.<br>
                                            <li> Be given clear and accurate information about their course, training and assessment arrangements and their progress.<br>
                                            <li> Access the support they need to effectively participate in their training program.<br>
                                            <li> Provide feedback to {{ $orgShortName }} on the client services, training, assessment and support services they receive.<br>
                                            <li> Be informed of any changes to agreed services, and how it affects them as soon as practicable.<br><br>

                                                <b>Student Responsibilities</b><br><br>

                                                All students, throughout their training and involvement with {{ $orgShortName }}, are expected to:<br><br>

                                            <li> Treat all people with fairness and respect and not do anything that could offend, embarrass or threaten others.<br>
                                            <li> Not harass, victimise, discriminate against or disrupt others.<br>
                                            <li> Treat all others and their property with respect.<br>
                                            <li> Respect the opinions and backgrounds of others. <br>
                                            <li> Follow all safety policies and procedures as directed by staff.<br>
                                            <li> Report any perceived safety risks, critical incident as they become known.<br>
                                            <li> Not bring into any premises being used for training purposes, any articles or items that may threaten the safety of self or others.<br>
                                            <li> If a government holiday falls on a class day, student should be ready to attend a make-up class on other days. If an intervention strategy requires, student <br>
                                        </p>
                                    </td>
                                </tr>

                            </table>

                        </div>
                    </div>
                    <div class="page">
                        <div id="pg1">
                            <table width="700px;" cellspacing="5px">
                                <tr>
                                    <td class="smallf" width="50%">
                                        <p>
                                            will be ready to attend additional classes beside regular scheduled classes.<br>
                                            <li> Provide relevant and accurate information to {{ $orgShortName }} in a timely manner.<br>
                                            <li> Approach their course with due personal commitment and integrity.<br>
                                            <li> Complete all assessment tasks, learning activities and assignments honestly and without plagiarism or infringing on copyright laws.<br>
                                            <li> Hand in all assessment tasks, assignments and other evidence of their work with a completed and signed cover sheet.<br>
                                            <li> Make regular contact with their Trainer/Assessor.<br>
                                            <li> Prepare appropriately for all assessment tasks on time, visits and training sessions.<br>
                                            <li> Notify {{ $orgShortName }} if any difficulties arise as part of their involvement in the program.<br>
                                            <li> Notify {{ $orgShortName }} if they are unable to attend a training session for any reason at least 12 hours prior to the commencement of the activity.<br>
                                            <li> Make payments for their training within agreed timeframes, where relevant.<br>
                                            <li> If the course student is studying has a workplace component, e.g. Childcare course, student has to manage the workplace him/herself. {{ $orgShortName }} staff will visit the workplace to check its compliance. If student fails to manage compliant workplace after enough efforts, {{ $orgShortName }} will arrange the work placement.<br>
                                            <li> Maintain satisfactory class attendance and course progress. Failing to pass 50% of assessments for two consecutive terms will result in being reported to Department of Education and will affect status of student visa. Attending less than 80% of class hours for two consecutive terms will result in being reported to Department of Education.<br><br>

                                                If you do not follow the above conduct requirements and housekeeping rules, you may be subject to disciplinary action such as suspension or a requirement to follow a disciplinary action plan.<br><br>

                                                <b>Complaints and Appeals Policy</b><br><br>

                                                <b>Policy</b><br><br>

                                                1. <b>Nature of complaints and appeals</b><br>


                                        </p>
                                    </td>
                                    <td class="smallf">
                                        <p>

                                            <li> {{ $orgShortName }} responds to all allegations involving the conduct of:<br>
                                                o {{ $orgShortName }}, its trainers and assessors and other staff.<br>
                                                o Any third-party providing Services on behalf of {{ $orgShortName }} and including education agents. <br>
                                                o Any student or client of {{ $orgShortName }}.<br>
                                                o Complaints may be made in relation to any of {{ $orgShortName }}'s services and activities such as:<br>
                                                o the application and enrolment process<br>
                                                o marketing information<br>
                                                o the quality of training/teaching and assessment provided<br>
                                                o training/teaching and assessment matters, including student progress, student support and assessment requirements<br>
                                                o the way someone has been treated<br>
                                                o the actions of another student<br>
                                                o An appeal is a request for a decision made by {{ $orgShortName }} to be reviewed. Decisions may have been about:<br>
                                                o course admissions <br>
                                                o refund assessments<br>
                                                o response to a complaint<br>
                                                o assessment outcomes / results<br>
                                                o other general decisions made by {{ $orgShortName }}<br><br>
                                                2. <b>Principles of resolution</b><br><br>
                                            <li> {{ $orgShortName }} is committed to developing a procedurally fair complaints and appeals process that is carried out free from bias, following the principles of natural justice. Through this policy and procedure, {{ $orgShortName }} ensures that complaints and appeals:<br>
                                                o Are responded to in a professional, consistent and transparent manner. <br>
                                                o Are responded to promptly, fairly, objectively, with sensitivity and confidentiality.<br>
                                                o Are able to be made at no cost to the individual. <br>
                                                o Are used as an opportunity to identify potential causes of the complaint or appeal and take actions to prevent the issues from recurring as well as identifying any areas for improvement.
                                                <br>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>
                    <div class="page">
                        <div id="pg1">
                            <table width="700px;" cellspacing="5px">
                                <tr>
                                    <td class="smallf" width="50%">
                                        <p>
                                            o {{ $orgShortName }} will inform all persons or parties involved in any allegations made as well as providing them with an opportunity to present their side of the matter.<br>
                                            o There are no charges for students to submit, a complaint or appeal to {{ $orgShortName }}, or to seek information or advice about doing so. <br>
                                            o Nothing in this policy and procedure limits the rights of an individual to take action under Australia's Consumer Protection laws and it does not circumscribe an individual's rights to pursue other legal remedies.<br>
                                            3. Making a complaint of appeal<br><br>
                                            <li> Complaints about a particular incident should be made as soon as possible after the incident occurring and appeals must be made within Seven (07) calendar days of the original decision being made. <br>

                                            <li> Complaints and appeals should be made in writing using the Complaints and Appeals Form, or other written format and sent to {{ $orgShortName }}'s administration office at {{ $orgAddress }}, attention to the Campus Manager(CM) or Chief Executive Officer (CEO). <br>
                                                When making a complaint or appeal, provide as much information as possible to enable {{ $orgShortName }} to investigate and determine an appropriate solution. <br> This should include:<br><br>
                                                o The issue you are complaining about or the decision you are appealing - describe what happened and how it affected you.<br>
                                                o Any evidence you haveto support your complaint or appeal.<br>
                                                o Details about the steps you have already taken to resolve the issue.<br>
                                                o Suggestions about how the matter might be resolved.<br>
                                                4. Timeframes for resolution<br>
                                            <li> The complaint or appeal will be acknowledged in writing within 3 business days.<br>
                                            <li> The complaints and appeals process will commence within 10 business days of receipt of the application. Complaints and appeals will be finalised as soon as practicable or at least <br><br>


                                        </p>
                                    </td>
                                    <td class="smallf">
                                        <p>

                                            within 30 calendar days unless there is a significant reason for the matter to take longer. <br>
                                            <li> In matters where additional time is needed, the complainant or appellant will be advised in writing of the reasons and will be updated weekly on the progress of the matter until such a time that the matter is resolved. <br>
                                                5. Resolution of complaints and appeals<br><br>
                                            <li> CM, CEO and other members of the management and administration team of {{ $orgShortName }} will be involved in resolving complaints and appeals as outlined in the procedures. <br>
                                            <li> Where a complaint or appeal involves another individual or organisation, they will be given the opportunity to respond to any allegations made.<br>
                                            <li> Where a third-party delivering Services on behalf of the {{ $orgShortName }} is involved, they will also be included in the process of resolving the complaint or appeal. <br>
                                            <li> Each party involved in the complaint or appeal may have a support person of their choice present at meetings scheduled to resolve the issue.<br>
                                            <li> In the case of an assessment appeal, an assessor who is independent from the original decision will assess the original task again. The outcome of this assessment will be the result granted for the assessment task. The complainant or appellant will be advised in writing of the outcome of the process and the reasons for the findings made.<br><br>
                                            <li> The enrolment status of student will be handled as follows: <br><br>
                                            <li> For international students, {{ $orgShortName }} will maintain a student's enrolment throughout the internal appeals processes without notifying DET via PRISMS of a change in enrolment status. In the case of an external appeals process it will depend on the type of appeal as to whether {{ $orgShortName }} maintains the student's enrolment as follows:<br><br>
                                            <li> If the appeal is against {{ $orgShortName }}'s decision to report the student for unsatisfactory course progress or attendance, the student's enrolment will be maintained until the external process is completed and has <br>
                                                <br>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>
                    <div class="page">
                        <div id="pg1">
                            <table width="700px;" cellspacing="5px">
                                <tr>
                                    <td class="smallf" width="50%">
                                        <p>
                                            supported or not supported {{ $orgShortName }}'s decision to report.<br>
                                            o If the appeal is against {{ $orgShortName }}'s decision to defer, suspend or cancel a student's enrolment due to misbehaviour, {{ $orgShortName }} will notify DET via PRISMS of a change to the student's enrolment after the outcome of the internal appeals process<br><br>
                                            6. Independent Parties<br><br>
                                            <li> {{ $orgShortName }} acknowledges the need for an appropriate independent party to be appointed to review a matter where this is requested by the complainant or appellant and the internal processes have failed to resolve the matter. Costs associated with independent parties to review a matter must be covered by the complainant/appellant unless the decision to include an independent party was made by {{ $orgShortName }}.<br>
                                                o For domestic students, the independent party recommended by {{ $orgShortName }} is Resolutions Institute, Level 1 and 2, 13-15 Bridge Street, Sydney NSW 2000, www.resolution.institute . However, complainants and appellants are able to use their own external party at their own cost. Domestic students may also access the external complaint avenues indicated below free of charge. <br>
                                                o For international students, the independent party is the Overseas Students Ombudsman. This service is free of charge. Where an international student is not satisfied with the outcome or conduct of the internal process, they are referred to the Overseas Students Ombudsman (OSO). See information under external complaint avenues. <br>
                                                o {{ $orgShortName }} will provide complete cooperation with the external mediator investigating the complaint/appeal and will be bound by the recommendations arising out of this process. <br>
                                                o The CM and CEO will ensure that any recommendations made are implemented within twenty (20) calendar days of being notified of the recommendations. The complainant or appellant will also be formally notified in writing of the outcome of the mediation, and any recommendations being actioned by {{ $orgShortName }}. <br><br>



                                        </p>
                                    </td>
                                    <td class="smallf">
                                        <p>

                                            7. External complaint avenues <br><br>
                                            <li> Complaints can also be made via the following avenues: <br>
                                            <li> National Training Complaints Hotline: <br><br>
                                                The National Training Complaints Hotline is a national service for consumers to register complaints concerning vocational education and training. The service refers consumers to the appropriate agency/authority/jurisdiction to assist with their complaint. Consumers can register a complaint with the National Training Complaints Hotline by: <br><br>
                                                o Phone: 13 38 73, Monday - Friday, 8am to 6pm nationally. <br>
                                                o Email: ntch@education.gov.au <br>
                                            <li> Australian Skills Quality Authority (ASQA): <br>
                                                Complainants may also complain to {{ $orgShortName }}'s registering body, Australian Skills Quality Authority (ASQA). ASQA can investigate complaints in relation to:<br><br>
                                            <li> the quality of our training and assessment<br>
                                            <li> our marketing and - advertising practices.<br><br>

                                                ASQA may not be able to investigate complaint if you do not include evidence that you have already exhausted our formal internal complaints process as above. If your complaint does not fall within ASQA's jurisdiction, it may be resolved more quickly if you directly contact the agency responsible as listed on the relevant webpage below. For more information, refer to the relevant webpage below before making a complaint to ASQA: <br><br>
                                                https://www.asqa.gov.au/complaints<br>
                                            <li> The Overseas Student Ombudsman (OSO)<br>
                                                International students may complain to the OSO if their complaint is in relation to {{ $orgShortName }}: <br><br>
                                                - refusing admission to a course<br>
                                                - course fees and refunds<br>
                                                - course or provider transfers<br>
                                                - course progress or attendance<br>
                                                - cancellation of enrolment<br>
                                                - accommodation or work arranged by your provider<br>

                                                <br>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>
                    <div class="page">
                        <div id="pg1">
                            <table width="700px;" cellspacing="5px">
                                <tr>
                                    <td class="smallf" width="50%">
                                        <p>
                                            - incorrect advice given by an education agent.<br><br>
                                            - if you believe we have failed to take action or are taking too long to take some action. This might include (for example), failing to provide your results in the normal timeframe, or failing to provide services included your written agreement with {{ $orgShortName }}.
                                            The OSO may not be able to investigate your complaint if you have not already exhausted our formal internal complaints process as above.<br><br>
                                            Please refer to the following website if you are considering making a complaint: http://www.ombudsman.gov.au/making-a-complaint/overseas-students#quality-of-education-provider<br><br>
                                            8. Records of complaints and appeals<br><br>
                                            {{ $orgShortName }} will maintain a record of all complaints and appeals and their outcomes and reasons for the outcomes on the Complaints and Appeals Register, which will be securely stored according to the Privacy Policy and Procedures.<br><br>
                                            Fees and Refund Policy<br><br>

                                            1. Fees and refund information<br>

                                            Prospective and current students are advised of the fees associated with a course on the relevant Course Outline and on the Student Agreement. In compliance with Clause 2 and 4 of the National Code 2018, this is provided prior to enrolment or commencement of training, whichever is first.<br>

                                            Refund information is outlined in the Student Agreement and in the Student Handbook. {{ $orgShortName }} publish in a prominent place on its website (i) All tuition and non-tuition fees (as shown on Course Outlines), (ii) This Fees and Refunds Policy.<br>

                                            The Student Agreement which is provided prior to enrolment and the International Student Handbook which is available in our website in a prominent place include this Fees and Refunds Policy and Procedures and informs the student of their consumer rights. Students are required to sign the Student Agreement in acknowledgement of the <br>


                                        </p>
                                    </td>
                                    <td class="smallf">
                                        <p>
                                            terms and conditions of the enrolment and this policy.<br>
                                            Fee information provided to the students includes: <br><br>
                                            - All course fees, including both tuition and non-tuition fees and the period to which these fees apply. <br>
                                            - Any additional charges that may apply and the circumstances in which they apply. <br>
                                            - The potential for changes to fees over the duration of the course. <br>
                                            - Payment options (including that international students may choose to pay more than 50% tuition fees before their course commences).<br>

                                            Fees will only be collected once a signed copy of the signed Student Agreement is received by {{ $orgShortName }}.<br><br>

                                            2. Inclusions in course fees<br><br>

                                            The Offer Letter and Agreement is clearly itemising tuition, as well as non-tuition fees.<br><br>

                                            <li> Course fees means the tuition fee, materials fee and other expenses (e.g. Application processing fee). Tuition Fee includes all of training/teaching and assessments required for the students to achieve the qualification or course in which they are enrolling within the attempts allowed. Material fees include perishable items, copies of textbook extract, hand-out and other mandatory learning materials, arranged by {{ $orgShortName }}. Any other textbook or reference book and materials that may need to be consulted but not necessarily required to be purchased, are not included in materials fees and will be mentioned as additional cost, should the student wish to purchase such materials. If text/library books are lost and need to be replaced, the student will be required to cover the cost of the replacement materials.<br><br>

                                            <li> Tuition fees include the issuance of one set of testamur and record of results and/or statement of attainment (in case of withdrawal or partial completion). For additional copies or re-issuing of any of these documents, an additional fee is applicable. Refer Schedule of Charges.<br><br>

                                            <li> Non-Tuition Fees include material fee, re-assessment fee, where a student fails to achieve a satisfactory outcome within deadlines, Fees for deferral of study, late payment of tuition fees, or other circumstances in which additional fees may apply as decided by {{ $orgShortName }}. This is outlined in the <br><br>

                                                <br>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>
                    <div class="page">
                        <div id="pg1">
                            <table width="700px;" cellspacing="5px">
                                <tr>
                                    <td class="smallf" width="50%">
                                        <p>
                                            table "Schedule of Additional Charges" attached to this document as well as in the International Student Handbook available in our website {{ $orgWebsite }} <br><br>

                                            <li> Course fees do not include Overseas Student Heath Cover or optional extras such as airport pick- ups; Direct debit setup; transaction and dishonour fees (where applicable); Credit card payment surcharges; stationaries like pen, pin; uniform etc. These fees will be additional costs as outlined in the Schedule of Charges, if applicable.<br><br>

                                                3. Payments<br><br>

                                            <li> Payments can be accepted by EFTPOS, electronic transfer, credit card, money order or direct debit. <br>
                                            <li> Credit card payments incur a surcharge per transaction.<br>
                                            <li> Students who are experiencing difficulty in paying their fees are invited to call our office to make alternative arrangements for payment during their period of difficulty. <br>
                                            <li> Students will be communicated before 14 calendar days of a payment due date. For delays in payment, an additional fee may be charged as late payment fee (consult schedule of charges). <br>
                                            <li> Debts will be referred to a debt collection agency where fees are more than 40 days past due. <br>
                                            <li> {{ $orgShortName }} reserves the right to suspend the provision of training and/or other services until fees are brought up to date. Students with long term outstanding accounts may be withdrawn from their course if payments have not been received and no alternative arrangements for payment have been made.<br>
                                            <li> International students who do not pay their fees will receive two warnings regarding non-payment of fees and thereafter will be reported to DET via PRISMS under student default.<br>
                                            <li> Receipts of payments made by international students will be kept for at least 2 years after the person ceases to be an accepted student.<br><br>


                                                4. Refunds <br><br>

                                                Students who withdraw from a course and wish to seek a refund or have the amount they owe on their fees reduced, must apply to {{ $orgShortName }} in writing, outlining the details and reason for their request. Students who have not completed a withdrawal form are not eligible for consideration of a refund or reduction in fees.<br>


                                        </p>
                                    </td>
                                    <td class="smallf">
                                        <p>
                                            Refund Process<br><br>
                                            Refund applications must be made in writing to the Principal Executive Officer (through contact details of SSM). Refunds are expected be paid from college's end in AUD without any accrued interest within 28 working days (but not later than 90 calendar days of application, if any banking/technical reason make it delayed) of receipt of a written application and will include a statement explaining how the refund was calculated. Student must provide own bank account details or indicate the specified person in the designated section of this agreement to receive the refund.<br><br>

                                            Students will be charged a non-refundable application processing fee / enrolment fee which is outlined on the fee section. This fee is non-refundable except in the unlikely situation where {{ $orgShortName }} is required to cancel a course for insufficient numbers, own inability to commence a course or for other unforeseen circumstances. In this case, students will receive a full refund of their application processing fee / enrolment fee.<br><br>

                                            Course Fee <br><br>
                                            Visa Refused<br>
                                            If an international student is refused a visa (student default) before commencing their course, {{ $orgShortName }} will refund the total amount of tuition and materials fee received for the course less 5% of the total amount of the fees or $500, whichever is the lower. However, the Application Processing Fee will not be refunded. <br>
                                            If an international student is refused a visa (student default) but has already commenced their course, non-tuition fees will not be refunded. However, tuition fees will be refunded from the day of the student default as per Section 7 of the Education Services for Overseas Students (Calculation of Refund) Specification 2014.
                                            Where a student is refused a visa and the reason for the refusal was because the student did not start the course at the location on the agreed starting day or the student withdrew from the course at that location or the student did not pay the fees due; there will be no refund.<br><br>
                                            100% refund of Course fees<br>
                                            <li> Where a course does not start <br>
                                                <br>

                                                <br>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>
                    <div class="page">
                        <div id="pg1">
                            <table width="700px;" cellspacing="5px">
                                <tr>
                                    <td class="smallf" width="50%">
                                        <p>
                                            date outlined in the Letter of Offer (provider default)<br><br>
                                            <li> If a student cannot commence the course because of illness, disability or where there is death of a close family member of the student (parent, sibling, spouse or child).<br><br>
                                            <li> At the discretion of {{ $orgShortName }}'s CEO or approved representative, when other special or extenuating circumstances have prevented the student from commencing their studies including political, civil or natural events.<br><br>
                                            <li> If an offer of a place is withdrawn by {{ $orgShortName }} and this is not due to incorrect or incomplete information being provided by the student.<br>
                                                80% refund of course fees<br><br>
                                                Where a student has not met the conditions included in the letter of offer and withdraws 28 or more days before class commencement, the course fees paid will be refunded after deducting 20% administration fee. Application Processing fee will not be refunded.
                                                Withdrawal for any other reason, notified in writing and received by {{ $orgShortName }} 28 Calendar days or more prior to class commencement will also result in refund of fees after deducting 20% administration fee. Application Processing fee will not be refunded.
                                                If a student has given incorrect or incomplete information and as a result {{ $orgShortName }} withdraws the offer prior to commencement of the course, the student will be eligible to receive a refund of all course fees paid after deducting 20% administration fee. Application Processing fee will not be refunded. <br><br>
                                                50% refund of course fees<br><br>
                                                Where a student withdraws the offer and the withdrawal is notified in writing and received by {{ $orgName }} within less than 28 calendar days prior to class commencement, the course fees paid, excluding the enrolment fee, will be refunded after deducting a 50% administration fee.<br>
                                                No refund of current semester course fees. <br>


                                                <br>


                                        </p>
                                    </td>
                                    <td class="smallf">
                                        <p><br>
                                            Withdrawals notified in writing and received by {{ $orgShortName }} on the commencement date or after the class commences of a unit/module, no refund of course fee for that unit/module. In this case, if the student has also paid for units/modules that have not been commenced yet, the refund will be calculated on a per unit or module cost. Tuition Fee of those units/modules will be refunded after deducting 20% administration fee and unutilized materials fees of those units/modules (total materials fees divided by the total number of units or clusters or modules in the course minus utilized portion)<br>
                                            Also, where {{ $orgShortName }} terminates the student's enrolment because of a failure to comply with {{ $orgShortName }}'s policies, for misbehaviour or unsatisfactory course progress, there will be no refund.
                                            <br>
                                            In the unlikely event that {{ $orgShortName }} is unable to deliver your course in full, you will be offered a refund for the portion of the course you have not received training for. The refund will be paid to you within 28 working days of the day on which the course ceased being provided. If {{ $orgShortName }} is unable to provide a refund or place you in an alternative course, our Tuition Protection Service (TPS) will place you in a suitable alternative course at no extra cost to you. Finally, if TPS cannot place you in a suitable alternative course or if there are no suitable alternative courses or offers, you may apply for a refund of the amount of any unspent pre-paid tuition fees you have paid to {{ $orgShortName }}. These are any tuition fees you have already paid that are directly related to the course/training which you haven't yet received. In the case of provider default there is no requirement for a student to lodge a refund application form. <br>
                                            Education Services for Overseas Students (Calculation of Refund) Specification 2014 may be consulted for calculating amount of refund for provider default or student default, if needed.
                                            Fees not listed in the refund section are not refundable. Prior to a student enrollment, tuition fees may be altered with or without notice. Once a student has completed enrolment, fees will not be subject to change for the normal duration of the course. If a course length is extended by the student, then any fee increases will be required to be paid for the extended component of the course.<br><br>
                                            5. Refund Process and Refund decisions
                                            <br>
                                            <br>

                                            <br>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>
                    <div class="page">
                        <div id="pg1">
                            <table width="700px;" cellspacing="5px">
                                <tr>
                                    <td class="smallf" width="50%">
                                        <p><br>
                                            Students who withdraw from a course may seek a refund or a reduction in fees owing by making an application for a refund in writing using the Application for Refund Form. The application must include the details and reason for the request. Students who have not completed a Withdrawal Form are not eligible for consideration of a refund or reduction in fees.<br><br>
                                            The refund assessment will be based on reviewing the services provided to the student and the costs incurred by {{ $orgName }} to provide those services.<br><br>

                                            The outcome of the refund assessment will be provided in writing to the student's registered address within 28 business days, outlining the decision and reasons for the decision along with any applicable refund or adjustment note. Refund decisions can be appealed following our Complaints and Appeals Policy and Procedure. <br><br>

                                            A student not achieving the qualification or unit/s in which they enrolled even after due to exhausting their attempt at reassessment, does not entitle the student to a refund. <br><br>
                                            Recording and payment of refunds<br><br>

                                            Refunds may be paid to the person or organisation that made the original payment.<br><br>
                                            Refund assessments can be appealed as per RTO's Complaints and Appeals Policy and Procedures. <br><br>
                                            Records of refund assessments and issuance of refunds will be stored securely on the student's file and in our accounts keeping system for a minimum duration of 2 years after the student ceases to be an enrolled student. <br><br>

                                            <br>
                                            <br><br>

                                            <br>


                                        </p>
                                    </td>
                                    <td class="smallf" valign="top">
                                        <p>Course Credit and Recognition of Prior Learning(RPL) <br><br>

                                            The decision to assess prior learning or grant course credit will preserve the integrity of the award to which it applies and comply with requirements of the underpinning educational framework of the course. If {{ $orgShortName }} grants the overseas student RPL or course credit that reduces the overseas student's course length, {{ $orgShortName }} will (i) inform the student of the reduced course duration following granting of RPL and ensure the confirmation of enrolment (CoE) is issued only for the reduced duration of the course (ii) will report any change in course duration in PRISMS, if RPL or course credit is granted after the overseas student's visa is granted.
                                            <br>
                                            <br>
                                        </p>
                                    </td>
                                </tr>

                            </table>
                            <table width="700px">
                                <tr>
                                    <td class="smallff">
                                        <b>Declaration.</b><br>

                                        I understand this Acceptance constitutes a written agreement with {{ $orgShortName }}. I have read and understood the terms and Conditions of Enrolment as detailed in the Acceptance Agreement, and I agree to abide by them. I declare that all information I have provided is true and correct and I am now paying the fees to confirm enrolment.
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallf" colspan="2" height="30"><br><br>

                                        _____________________________ &nbsp;&nbsp;__________________________<br><br>

                                        Signature of Student* &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/<br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="smallff">
                                        Student's signature must be the same as on their passport (please supply a copy)<br>
                                        <br>

                                        Please return your signed course Acceptance Agreement and completed Payment Details page with evidence of your payment to the Admissions Coordinator email at {{ $company->email }} or to a relevant campus at the address shown below:<br><br>

                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>

        <tfoot>
            <tr>
                <td>
                    <!--place holder for the fixed-position footer-->
                    <div class="page-footer-space"></div>
                </td>
            </tr>
        </tfoot>

    </table>

</body>

</html>