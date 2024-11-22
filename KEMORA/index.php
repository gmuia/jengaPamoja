<?php
ob_start(); // Start output buffering
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
require_once 'Database.php';
session_start();

// Generate CSRF token if it doesn't already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];
$database = new Database();
$conn = $database->connect();

$counties_query = "SELECT * FROM counties";
$house_types_query = "SELECT * FROM house_types";
$payment_plans_query = "SELECT * FROM payment_plans";
$mortgage_plans_query = "SELECT * FROM mortgage_plans";
$genders_query = "SELECT * FROM genders";
$relationship_statuses_query = "SELECT * FROM relationship_statuses";

try {
    $counties_statement = $conn->prepare($counties_query);
    $counties_statement->execute();
    $counties = $counties_statement->fetchAll(PDO::FETCH_ASSOC);

    $house_types_statement = $conn->prepare($house_types_query);
    $house_types_statement->execute();
    $house_types = $house_types_statement->fetchAll(PDO::FETCH_ASSOC);

    $payment_plans_statement = $conn->prepare($payment_plans_query);
    $payment_plans_statement->execute();
    $payment_plans = $payment_plans_statement->fetchAll(PDO::FETCH_ASSOC);

    $mortgage_plans_statement = $conn->prepare($mortgage_plans_query);
    $mortgage_plans_statement->execute();
    $mortgage_plans = $mortgage_plans_statement->fetchAll(PDO::FETCH_ASSOC);

    $genders_statement = $conn->prepare($genders_query);
    $genders_statement->execute();
    $genders = $genders_statement->fetchAll(PDO::FETCH_ASSOC);

    $relationship_statuses_statement = $conn->prepare($relationship_statuses_query);
    $relationship_statuses_statement->execute();
    $relationship_statuses = $relationship_statuses_statement->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Get errors and form data if available
$quotation_errors = $_SESSION['quotation_errors'] ?? [];
$registration_errors = $_SESSION['registration_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];

// Clear session variables after using them
unset($_SESSION['quotation_errors'], $_SESSION['form_data']);
unset($_SESSION['registration_errors'], $_SESSION['form_data']);

// Other code...
ob_end_flush(); // End output buffering
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
    <title>Omohhomes - Jenga Pamoja</title>
    <!-- Icon that shows in the browser tab when the platform is launched -->
    <link rel="shortcut icon" href="images/logos/logo.png">
    <meta name="referrer" content="no-referrer">
    <!-- Scripts -->
    <script src="js/jengaPamoja.js" defer></script>
    <!-- Script handler for github buttons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/github-buttons/2.27.0/buttons.min.js" integrity="sha512-+FbBfouZ1f3s3mNjA1PLjgJ+NNKq1+Ic8523WvBdiZ3bxpxVRVydh5+gXPZWz0SXHPQ/8gZTl99hxAfRc4g2BA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Script handler for JQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Script for full calendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <!-- Script handler for Popper Js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js" integrity="sha512-TPh2Oxlg1zp+kz3nFA0C5vVC6leG/6mm1z9+mA81MI5eaUVqasPLO8Cuk4gMF4gUfP5etR73rgU/8PNMsSesoQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Commented out these 2 bootstrap js links which were causing the issue with the nav bar collapse button not collapsing the navbar after its opened in mobile view -->
    <!-- Script handler for Bootstrap Js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js" integrity="sha512-ykZ1QQr0Jy/4ZkvKuqWn4iF3lqPZyij9iRv6sGqLRdTPkY69YX6+7wvVGmsdBbiIfN/8OdsI7HABjvEok6ZopQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Bootstrap script that controls dropdown boxes -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" defer></script> -->
    <!-- Script handler for waypoints -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/noframework.waypoints.min.js" integrity="sha512-fHXRw0CXruAoINU11+hgqYvY/PcsOWzmj0QmcSOtjlJcqITbPyypc8cYpidjPurWpCnlB8VKfRwx6PIpASCUkQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Script sources for ripple.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ripple.js/1.2.1/ripple.js" integrity="sha512-wquKjza9uz7HBX/wy2wQVIq0VZrjKbKqsUSPeHHjEc3lOsEf1xRAoEt5+/89K1P1Ch+hhTlE+EUqKMAEtp6Usg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ripple.js/1.2.1/ripple.min.js" integrity="sha512-M7LdVdj6Pck0GDllHuEchDVXzPPvss3VSn3QSgBUcVLgLYq+bPCj91xKRfUSwjF/wmClLJUHEwm+p/d3OmBbtw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Script sources for chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome Icons JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js" integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Select2 search library for searching select drop down fields -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Google translator script -->
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <!-- Data Tables script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js" integrity="sha512-BkpSL20WETFylMrcirBahHfSnY++H2O1W+UnEEO4yNIl+jI2+zowyoGJpbtk6bx97fBXf++WJHSSK2MV4ghPcg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" defer></script>

    <!-- CSS script for select2 search library for searching select drop down fields -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- CSS script source for Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Font Awesome Icons CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Data Tables Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css" integrity="sha512-1k7mWiTNoyx2XtmI96o+hdjP8nn0f3Z2N4oF/9ZZRgijyV4omsKOXEnqL1gKQNPy2MTSP9rIEWGcH/CInulptA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Styles -->
    <link href="css/jengaPamoja.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid" id="jengaPamojaContainer">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                            <a class="navbar-brand" href="https://omohhomes.com/">
                                <img src="images/logos/logo.png" class="ms-3" id="mainNavLogo">
                            </a>

                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>

                            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                    <li class="nav-item active">
                                        <a class="nav-link" href="https://omohhomes.com/">OMOHHOMES</a>
                                    </li>

                                    <!-- <li class="nav-item">
                                        <a class="nav-link" href="#">Features</a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link" href="#">Pricing</a>
                                    </li>

                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Dropdown link
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </li> -->
                                </ul>

                                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 pe-5">
                                    <li class="nav-item active">
                                        <a class="nav-link" href="https://www.kemorake.org/">KEMORA</a>
                                    </li>
                                </ul>

                                <a class="navbar-brand" href="https://www.kemorake.org/">
                                    <img src="images/partners/KEMORA.png" class="ms-3" id="mainNavLogo">
                                </a>
                            </div>
                        </nav>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <?php
                            // Check if a status message exists
                            if (isset($_SESSION['status']) && isset($_SESSION['message'])):
                                $alertType = $_SESSION['status'] === 'SUCCESS' ? 'alert-success' : 'alert-danger';
                            ?>

                            <div class="alert <?php echo $alertType; ?>">
                                <span><?php echo $_SESSION['message']; ?></span>

                                <button type="button" class="btn btn-close float-end" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <?php
                            // Clear the session message after displaying it
                            unset($_SESSION['status'], $_SESSION['message']);
                            ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-1 col-lg-1 col-xl-1 col-xxl-1"></div>

                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-xxl-5">

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                <h1 class="text-success display-1" id="jenga_pamoja_main_text">Jenga Pamoja</h1>
                                <h2 class="text-warning-secondary display-2" id="housing_package_main_text"><b>Housing Package</b></h2>

                                <p><b>Omoh Homes</b> provides:
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <p>
                                                <span>&#8226;</span>
                                                <span>Unique</span>
                                            </p>
                                        </li>

                                        <li class="list-group-item">
                                            <p>
                                                <span>&#8226;</span>
                                                <span>Dynamic</span>
                                            </p>
                                        </li>

                                        <li class="list-group-item">
                                            <p>
                                                <span>&#8226;</span>
                                                <span>Affordable/Low Cost</span>
                                            </p>
                                        </li>
                                    </ul>
                                </p>

                                <p><b>Housing solutions</b> under the <span class="text-uppercase"><b>jenga pamoja housing package</b></span></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                <a href="javascript:void(0);" id="registrationButton" class="btn btn-success rounded-pill text-uppercase me-3" title="Register Now">Register Now</a>

                                <a href="javascript:void(0);" id="quotationButton" class="btn btn-success rounded-pill text-uppercase me-3" title="Request Quote">Request Quote</a>

                                <a href="bronchures/OMOH-HOMES-JENGA-PAMOJA-BRONCHURE.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Bronchure" download>Download Bronchure</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-xxl-5">
                        <img src="images/housePlan.jpeg" id="housePlanImage">
                    </div>

                    <div class="col-sm-12 col-md-1 col-lg-1 col-xl-1 col-xxl-1"></div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <div id="houseDesignsWrapper">
                            <!-- Background Layer -->
                            <div id="houseDesignsOptions"></div>

                            <!-- Content Layer -->
                            <div class="row" id="houseDesignsContent">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                    <h2 class="text-center display-5 mt-3">
                                        <span class="text-success">House</span>
                                        <span class="text-warning-secondary">Designs</span>
                                    </h2>

                                    <div class="row pt-3">
                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                            <div class="card rounded-pill" id="houseDesignCards">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div clas="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <h5 class="display-6 text-center">One Bedroom</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                            <div class="card rounded-pill" id="houseDesignCards">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div clas="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <h5 class="display-6 text-center">Two Bedroom</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                            <div class="card rounded-pill" id="houseDesignCards">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div clas="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <h5 class="display-6 text-center">Three Bedroom</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h2 class="text-center display-5 mt-3">
                            <span class="text-success" id="ownText">Own</span> <span class="text-warning-secondary">a House</span>
                        </h2>

                        <div class="row">
                            <div class="col-sm-12 col-md-1 col-lg-1 col-xl-1 col-xxl-1"></div>

                            <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-xxl-5">
                                <img src="images/housePlan_2.jpg" id="housePlanImage">
                            </div>

                            <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-xxl-5">
                                <p class="mt-5">
                                    <span>At <b>OMOH HOMES</b>, we understand that everyone’s journey to <b>homeownership</b> is <b>unique</b>.
                                    That’s why we offer <b>3 tailored options</b> to make <b>owning your dream home</b> a <b>reality</b>.
                                    Whether you’re ready to <b>invest upfront, prefer a gradual transition</b>, or <b>need flexible financing</b>,
                                    we’ve got a plan that fits your needs</span>

                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <b>Buy to Own:-</b>
                                            <span>This option is perfect for those ready to make a full investment upfront. With Buy to Own,
                                                you gain immediate ownership of your dream home by purchasing it outright.
                                                It’s a straightforward process with no ongoing obligations beyond maintaining your property.</span>
                                        </li>

                                        <li class="list-group-item">
                                            <b>Rent to Own:-</b>
                                            <span>This plan offers flexibility for those not yet ready to buy. Start by renting the home,
                                                with a portion of your monthly payments going toward its eventual purchase.
                                                It’s an excellent way to work toward ownership while enjoying the benefits of living in your desired home.</span>
                                        </li>

                                        <li class="list-group-item">
                                            <b>Mortgage:-</b>
                                            <span>For those seeking financing options, our Mortgage plan provides a manageable way to own your home over time.
                                                Work with our trusted lenders to secure a loan, make affordable monthly payments, and gradually build equity in your home.</span>
                                        </li>
                                    </ul>
                                </p>
                            </div>

                            <div class="col-sm-12 col-md-1 col-lg-1 col-xl-1 col-xxl-1"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <div id="paymentPlanOptionsWrapper">
                            <!-- Background Layer -->
                            <div id="paymentPlanOptions"></div>

                            <!-- Content Layer -->
                            <div class="row" id="paymentPlanContent">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                    <h2 class="text-center display-5 mt-3">
                                        <span class="text-success">Payment</span>
                                        <span class="text-warning-secondary">Plan Options</span>
                                    </h2>

                                    <div class="row">
                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3"></div>

                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3">
                                            <div class="card" id="paymentPlanOptionsCards">
                                                <div class="card-body p-0">
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <img src="images/BTO.png" id="paymentPlanOptionsImage">
                                                        </div>
                                                    </div>

                                                    <div class="row pt-3 pb-3">
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                                            <a href="bronchures/OMOH-BTO-FLYER.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Buy To Own Bronchure" download>Download Buy To Own Bronchure</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3">
                                            <div class="card" id="paymentPlanOptionsCards">
                                                <div class="card-body p-0">
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <img src="images/RTO.png" id="paymentPlanOptionsImage">
                                                        </div>
                                                    </div>

                                                    <div class="row pt-3 pb-3">
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                                            <a href="bronchures/OMOH-RTO-FLYER.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Rent To Own Bronchure" download>Download Rent To Own Bronchure</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>

                    <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                        <div class="row">
                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4"></div>

                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                <p>
                                    Homeownership is more than just acquiring a property—it's about:
                                    <ol>
                                        <li>
                                            <span>Building a legacy</span>
                                        </li>

                                        <li>
                                            <span>Finding security</span>
                                        </li>

                                        <li>
                                            <span>Creating a space where memories flourish</span>
                                        </li>
                                    </ol>
                                </p>

                                <p>
                                    At <strong>Omoh Homes</strong>, we don’t just offer houses;
                                    <ol>
                                        <li>
                                            <span>We deliver lifestyles</span>
                                        </li>

                                        <li>
                                            <span>We deliver dreams</span>
                                        </li>

                                        <li>
                                            <span>We deliver opportunities tailored to your journey</span>
                                        </li>
                                    </ol>
                                </p>
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4"></div>
                        </div>

                        <ul class="list-group">
                            <li class="list-group-item">
                                <p>
                                    <strong>🏡 Your Home, Your Haven:</strong> Whether it’s the cozy corner where your mornings start or the vibrant living room that hosts family celebrations, owning a home gives you the freedom to live life on your terms.
                                </p>
                            </li>

                            <li class="list-group-item">
                                <p>
                                    <strong>🔑 Unlock Endless Possibilities:</strong> With Omoh Homes, you’re not just getting walls and a roof; you’re stepping into a future of financial stability and personal empowerment. Homeownership puts the power in your hands—build equity, create wealth, and leave something behind for the next generation.
                                </p>
                            </li>

                            <li class="list-group-item">
                                <p>
                                    <strong>🌟 It’s About You:</strong> We know every homeowner's dream is unique. That’s why we’re here to listen, guide, and ensure your journey to ownership is smooth, personalized, and truly rewarding.
                                </p>
                            </li>

                            <li class="list-group-item">
                                <p>
                                    <strong>🌱 A Foundation for Growth:</strong> A home isn’t just a place; it’s a springboard for your ambitions. It provides stability and the confidence to dream bigger, knowing that you’ve secured something tangible for your future.
                                </p>
                            </li>

                            <li class="list-group-item">
                                <p>
                                    <strong>❤️ Designed with You in Mind:</strong> At Omoh Homes, we believe a home is where life’s best moments are created. It’s where you can unwind, feel safe, and build connections with your loved ones.
                                </p>
                            </li>
                        </ul>

                        <p class="text-center mt-3">
                            Make the move today. <strong>Your future deserves the stability, security, and pride that comes with homeownership.</strong> Let Omoh Homes help you build that future.
                        </p>
                    </div>

                    <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <div id="mortgagePlanOptionsWrapper">
                            <!-- Background Layer -->
                            <div id="mortgagePlanOptions"></div>

                            <!-- Content Layer -->
                            <div class="row" id="mortgagePlanOptionsContent">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                    <h2 class="text-center display-5 mt-3">
                                        <span class="text-success">Mortgage</span>
                                        <span class="text-warning-secondary">Plan Options</span>
                                    </h2>

                                    <div class="row mt-3">
                                        <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>

                                        <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                                    <div class="card" id="mortgagePlanOptionsCards">
                                                        <div class="card-body p-0">
                                                            <div class="row">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                                    <img src="images/individual-investment-package.png" id="mortgagePlanOptionsImage">
                                                                </div>
                                                            </div>

                                                            <div class="row pt-3 pb-3">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                                                    <a href="bronchures/Individual-Investment-Package.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Bronchure" download>Download Bronchure</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                                    <div class="card" id="mortgagePlanOptionsCards">
                                                        <div class="card-body p-0">
                                                            <div class="row">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                                    <img src="images/group-investment-package.png" id="mortgagePlanOptionsImage">
                                                                </div>
                                                            </div>

                                                            <div class="row pt-3 pb-3">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                                                    <a href="bronchures/Group-Investment-Package.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Bronchure" download>Download Bronchure</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                                    <div class="card" id="mortgagePlanOptionsCards">
                                                        <div class="card-body p-0">
                                                            <div class="row">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                                    <img src="images/corporate-investment-package.png" id="mortgagePlanOptionsImage">
                                                                </div>
                                                            </div>

                                                            <div class="row pt-3 pb-3">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                                                    <a href="bronchures/Corporate-Investment-Package.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Bronchure" download>Download Bronchure</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h2 class="text-center display-5 mt-3">
                            <span class="text-success" id="ownText">Our</span> <span class="text-warning-secondary">Partners</span>
                        </h2>

                        <div class="row mt-3">
                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4"></div>

                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 d-flex justify-content-center">
                                <img src="images/partners/KEMORA.png" id="ourPartnersImage">
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4"></div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <div id="chooseUsWrapper">
                            <!-- Background Layer -->
                            <div id="chooseUs"></div>

                            <!-- Content Layer -->
                            <div class="row" id="chooseUsContent">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                    <h2 class="text-center display-5 mt-3">
                                        <span class="text-success">Why</span>
                                        <span class="text-warning-secondary">Choose Us</span>
                                    </h2>

                                    <div class="row mt-3">
                                        <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>

                                        <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    <span><strong>Tailored Solutions</strong></span>
                                                    <span>We understand that every homeowner’s journey is unique. Our plans are flexible and designed to suit your individual needs and goals.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Affordable Pathways</strong></span>
                                                    <span>From Rent to Own to Mortgage options, we make homeownership accessible by offering payment plans that fit your budget.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Transparency at Every Step</strong></span>
                                                    <span>No hidden fees, no surprises—just honest, clear communication throughout the home-buying process.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Quality Homes</strong></span>
                                                    <span>Our properties are built to last, combining modern design with functionality to give you a home you’ll love for years to come.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Trusted Partners</strong></span>
                                                    <span>We work with reliable lenders, builders, and industry professionals to ensure your journey to homeownership is smooth and stress-free.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Dedicated Support</strong></span>
                                                    <span>Our team is here to guide you every step of the way, providing expert advice and personalized assistance.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Future Investment</strong></span>
                                                    <span>Owning a home with Omoh Homes is more than a purchase—it’s an investment in stability, equity, and generational wealth.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Community Building</strong></span>
                                                    <span>We’re committed to creating vibrant, thriving neighborhoods where you and your family can grow and connect.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Your Vision, Our Priority</strong></span>
                                                    <span>We prioritize your needs and work tirelessly to deliver homes that align with your aspirations and lifestyle.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Unmatched Value</strong></span>
                                                    <span>With competitive pricing and a focus on quality, Omoh Homes ensures you get the best value for your investment.</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

                        <!-- Section Header -->
                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                <h2 class="text-center display-5 mt-3">
                                    <span class="text-success">We Are Here To Answer</span>
                                    <br>
                                    <span class="text-warning-secondary">All Your Questions</span>
                                </h2>
                            </div>
                        </div>

                        <!-- FAQ Accordion -->
                        <div class="row mt-4">
                            <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>

                            <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                                <div class="accordion" id="faqAccordion">
                                    <!-- Question 1 -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading1">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                                                What is the process for owning a home with Omoh Homes?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Our process is simple and personalized. Choose a plan that suits you, work with our team to finalize details, and move into your dream home. Whether it's Buy to Own, Rent to Own, or Mortgage, we guide you every step of the way.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Question 2 -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading2">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                                Do you offer flexible payment options?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Yes! We offer flexible payment plans including Rent to Own, Mortgage options, and outright Buy to Own to ensure you find a solution that fits your budget.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Question 3 -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading3">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                                How do I qualify for a Rent to Own plan?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Qualifying for Rent to Own is straightforward. You’ll need proof of income, a rental agreement, and a willingness to work toward full ownership. A portion of your rent will contribute toward your future purchase.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Question 4 -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading4">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                                Are there any hidden fees or charges?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                No. At Omoh Homes, transparency is key. All costs and terms are clearly outlined upfront, so there are no hidden surprises during your journey to homeownership.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Question 5 -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading5">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                                                Can I customize my home after purchasing it?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Absolutely! Once you own your home, you have the freedom to customize it to your taste, turning it into a space that reflects your personality and lifestyle.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>
                        </div>

                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <div style="width: 100%; height: 600px;">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.8183069637935!2d36.81336027573141!3d-1.2828416356239432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f10d23eb49bbb%3A0xf5ebdd7d189417a1!2sView%20Park%20Towers!5e0!3m2!1sen!2ske!4v1732051575504!5m2!1sen!2ske"
                                width="100%"
                                height="100%"
                                style="border: 0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

                        <div class="row">
                            <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>

                            <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                                <div class="card shadow rounded mt-5 mb-5" id="registrationCard">
                                    <div class="card-header bg-success">
                                        <h5 class="card-title text-uppercase text-white"><b>Registration Form</b></h5>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                <p>
                                                    <b>N.B:-</b>
                                                    <span>Please be <b class="text-uppercase">informed</b> that <b class="text-uppercase">registration costs 2000 K.shs</b></span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                <form action="register.php" method="POST" id="registrationForm" role="form" accept-charset="UTF-8">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                                                    <div class="row">
                                                        <!-- First Name -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="first_name" class="form-label"><b class="text-uppercase">First Name:</b></label>
                                                            <input type="text" name="first_name" class="form-control border border-dark <?php echo isset($registration_errors['first_name']) ? 'is-invalid' : ''; ?>" id="first_name" placeholder="John" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" title="Insert your first name" minlength="1" maxlength="255" autofocus>
                                                            <?php if (isset($registration_errors['first_name'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['first_name']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Last Name -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="middle_name" class="form-label"><b class="text-uppercase">Middle Name:</b></label>
                                                            <input type="text" name="middle_name" class="form-control border border-dark <?php echo isset($registration_errors['middle_name']) ? 'is-invalid' : ''; ?>" id="middle_name" placeholder="Doe" value="<?php echo htmlspecialchars($_POST['middle_name'] ?? ''); ?>" title="Insert your middle name" minlength="1" maxlength="255" autofocus>
                                                            <?php if (isset($registration_errors['middle_name'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['middle_name']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Surname -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="surname" class="form-label"><b class="text-uppercase">Surname:</b></label>
                                                            <input type="text" name="surname" class="form-control border border-dark <?php echo isset($registration_errors['surname']) ? 'is-invalid' : ''; ?>" id="surname" placeholder="Smith" value="<?php echo htmlspecialchars($_POST['surname'] ?? ''); ?>" title="Insert your surname" minlength="1" maxlength="255" autofocus>
                                                            <?php if (isset($registration_errors['surname'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['surname']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- ID Number -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="id_number" class="form-label"><b class="text-uppercase">ID Number/Passport No/Military No:</b></label>
                                                            <input type="text" name="id_number" class="form-control border border-dark <?php echo isset($registration_errors['id_number']) ? 'is-invalid' : ''; ?>" id="id_number" placeholder="Enter ID Number" value="<?php echo htmlspecialchars($_POST['id_number'] ?? ''); ?>" minlength="1" maxlength="55" title="Enter your ID number" autofocus>
                                                            <?php if (isset($registration_errors['id_number'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['id_number']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- KRA PIN -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="kra_pin" class="form-label"><b class="text-uppercase">KRA Pin:</b></label>
                                                            <input type="text" name="kra_pin" class="form-control border border-dark <?php echo isset($registration_errors['kra_pin']) ? 'is-invalid' : ''; ?>" id="kra_pin" placeholder="Enter KRA PIN" value="<?php echo htmlspecialchars($_POST['kra_pin'] ?? ''); ?>" minlength="1" maxlength="55" title="Enter your KRA PIN" autofocus>
                                                            <?php if (isset($registration_errors['kra_pin'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['kra_pin']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- County -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="county" class="form-label"><b class="text-uppercase">County:</b></label>
                                                            <select name="county" id="county" class="form-select border border-dark <?php echo isset($registration_errors['county']) ? 'is-invalid' : ''; ?>">
                                                                <option value="" disabled selected>Select County</option>
                                                                <?php if ($counties): ?>
                                                                    <?php foreach ($counties as $county): ?>
                                                                        <option value="<?php echo htmlspecialchars($county['id']); ?>" <?php echo ($_POST['county'] ?? '') == $county['id'] ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($county['county_name']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <option value="" disabled>No counties available</option>
                                                                <?php endif; ?>
                                                            </select>
                                                            <?php if (isset($registration_errors['county'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['county']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- City -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="city" class="form-label"><b class="text-uppercase">City:</b></label>
                                                            <input type="text" name="city" class="form-control border border-dark <?php echo isset($registration_errors['city']) ? 'is-invalid' : ''; ?>" id="city" placeholder="Enter city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your city" autofocus>
                                                            <?php if (isset($registration_errors['city'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['city']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Town -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="town" class="form-label"><b class="text-uppercase">Town:</b></label>
                                                            <input type="text" name="town" class="form-control border border-dark <?php echo isset($registration_errors['town']) ? 'is-invalid' : ''; ?>" id="town" placeholder="Enter town" value="<?php echo htmlspecialchars($_POST['town'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your town" autofocus>
                                                            <?php if (isset($registration_errors['town'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['town']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Date of Birth -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="date_of_birth" class="form-label"><b class="text-uppercase">Date of Birth:</b></label>
                                                            <input type="date" name="date_of_birth" class="form-control border border-dark <?php echo isset($registration_errors['date_of_birth']) ? 'is-invalid' : ''; ?>" id="date_of_birth" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>" title="Enter your date of birth">
                                                            <?php if (isset($registration_errors['date_of_birth'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['date_of_birth']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- Age -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="age" class="form-label"><b class="text-uppercase">Age:</b></label>
                                                            <input type="number" name="age" class="form-control border border-dark <?php echo isset($registration_errors['age']) ? 'is-invalid' : ''; ?>" id="age" value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>" placeholder="18" title="Enter your age">
                                                            <?php if (isset($registration_errors['age'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['age']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Email -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="email" class="form-label"><b class="text-uppercase">Email:</b></label>
                                                            <input type="email" name="email" class="form-control border border-dark <?php echo isset($registration_errors['email']) ? 'is-invalid' : ''; ?>" id="email" placeholder="Enter email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter a valid email address" autofocus>
                                                            <?php if (isset($registration_errors['email'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['email']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Member Number -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="member_number" class="form-label"><b class="text-uppercase">KEMORA Member Number:</b></label>
                                                            <input type="text" name="member_number" class="form-control border border-dark <?php echo isset($registration_errors['member_number']) ? 'is-invalid' : ''; ?>" id="member_number" placeholder="Enter member number" value="<?php echo htmlspecialchars($_POST['member_number'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your member number" autofocus>
                                                            <?php if (isset($registration_errors['member_number'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['member_number']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- Phone Number -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="phone_number" class="form-label"><b class="text-uppercase">Phone Number:</b></label>
                                                            <input type="text" name="phone_number" class="form-control border border-dark <?php echo isset($registration_errors['phone_number']) ? 'is-invalid' : ''; ?>" id="phone_number" placeholder="Enter phone number" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>" minlength="8" maxlength="15" title="Enter your phone number" autofocus>
                                                            <?php if (isset($registration_errors['phone_number'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['phone_number']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Payment Plan -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="payment_plan" class="form-label"><b class="text-uppercase">Payment Plan:</b></label>
                                                            <select name="payment_plan" id="payment_plan" class="form-select border border-dark <?php echo isset($registration_errors['payment_plan']) ? 'is-invalid' : ''; ?>" autofocus>
                                                                <option value="" disabled selected>Select payment plan</option>
                                                                <?php if ($payment_plans): ?>
                                                                    <?php foreach ($payment_plans as $payment_plan): ?>
                                                                        <option value="<?php echo htmlspecialchars($payment_plan['id']); ?>" <?php echo ($_POST['payment_plan'] ?? '') == $payment_plan['id'] ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($payment_plan['plan_name']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <option value="" disabled>No payment plans available</option>
                                                                <?php endif; ?>
                                                            </select>
                                                            <?php if (isset($registration_errors['payment_plan'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['payment_plan']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Mortgage Plan -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="mortgage_plan" class="form-label"><b class="text-uppercase">Mortgage Plan:</b></label>
                                                            <select name="mortgage_plan" id="mortgage_plan" class="form-select border border-dark <?php echo isset($registration_errors['mortgage_plan']) ? 'is-invalid' : ''; ?>" autofocus>
                                                                <option value="" disabled selected>Select mortgage plan</option>
                                                                <?php if ($mortgage_plans): ?>
                                                                    <?php foreach ($mortgage_plans as $mortgage_plan): ?>
                                                                        <option value="<?php echo htmlspecialchars($mortgage_plan['id']); ?>" <?php echo ($_POST['mortgage_plan'] ?? '') == $mortgage_plan['id'] ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($mortgage_plan['plan_name']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <option value="" disabled>No mortgage plans available</option>
                                                                <?php endif; ?>
                                                            </select>
                                                            <?php if (isset($registration_errors['mortgage_plan'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['mortgage_plan']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- Gender -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="gender" class="form-label"><b class="text-uppercase">Gender:</b></label>
                                                            <select name="gender" id="gender" class="form-select border border-dark <?php echo isset($registration_errors['gender']) ? 'is-invalid' : ''; ?>" autofocus>
                                                                <option value="" disabled selected>Select gender</option>
                                                                <?php if ($genders): ?>
                                                                    <?php foreach ($genders as $gender): ?>
                                                                        <option value="<?php echo htmlspecialchars($gender['id']); ?>" <?php echo ($_POST['gender'] ?? '') == $gender['id'] ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($gender['gender_identity']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <option value="" disabled>No gender identities available</option>
                                                                <?php endif; ?>
                                                            </select>
                                                            <?php if (isset($registration_errors['gender'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['gender']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Relationship Status -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="relationship_status" class="form-label"><b class="text-uppercase">Relationship Status:</b></label>
                                                            <select name="relationship_status" id="relationship_status" class="form-select border border-dark <?php echo isset($registration_errors['relationship_status']) ? 'is-invalid' : ''; ?>" autofocus>
                                                                <option value="" disabled selected>Select relationship status</option>
                                                                <?php if ($relationship_statuses): ?>
                                                                    <?php foreach ($relationship_statuses as $relationship_status): ?>
                                                                        <option value="<?php echo htmlspecialchars($relationship_status['id']); ?>" <?php echo ($_POST['relationship_status'] ?? '') == $relationship_status['relationship_status'] ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($relationship_status['relationship_status']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <option value="" disabled>No relationship statuses available</option>
                                                                <?php endif; ?>
                                                            </select>
                                                            <?php if (isset($registration_errors['relationship_status'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['relationship_status']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3"></div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- Promotional Emails -->
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-1">
                                                            <input type="checkbox" name="promotional_emails" id="promotional_emails" class="border border-dark <?php echo isset($registration_errors['promotional_emails']) ? 'is-invalid' : ''; ?>" value="1" <?php echo isset($_POST['promotional_emails']) ? 'checked' : ''; ?> autofocus>
                                                            <label class="form-check-label" for="promotional_emails">Yes, I'd like to receive promotional emails with offers and updates.</label>
                                                            <?php if (isset($registration_errors['promotional_emails'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['promotional_emails']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- Exclusive Emails -->
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-1">
                                                            <input type="checkbox" name="exclusive_emails" id="exclusive_emails" class="border border-dark <?php echo isset($registration_errors['exclusive_emails']) ? 'is-invalid' : ''; ?>" value="1" <?php echo isset($_POST['exclusive_emails']) ? 'checked' : ''; ?> autofocus>
                                                            <label class="form-check-label" for="exclusive_emails">Yes, I'd like to receive exclusive emails with discounts and product information.</label>
                                                            <?php if (isset($registration_errors['exclusive_emails'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['exclusive_emails']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <button type="submit" name="submit_registration" class="btn btn-success text-uppercase text-white float-end rounded-pill">Submit</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card shadow rounded mt-5 mb-5" id="quotationCard">
                                    <div class="card-header bg-success">
                                        <h5 class="card-title text-uppercase text-white"><b>Quotation Form</b></h5>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                <form action="quotation.php" method="POST" id="quotationForm" role="form" accept-charset="UTF-8">
                                                    <!-- CSRF Token -->
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                                                    <div class="row">
                                                        <!-- Full Names -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="full_names" class="form-label"><b class="text-uppercase">Full Names:</b></label>
                                                            <input type="text" name="full_names" class="form-control border border-dark <?php echo isset($quotation_errors['full_names']) ? 'is-invalid' : ''; ?>" id="full_names" placeholder="John Doe" value="<?php echo htmlspecialchars($_POST['full_names'] ?? ''); ?>" title="Insert your full name" minlength="1" maxlength="255" autofocus>
                                                            <?php if (isset($quotation_errors['full_names'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['full_names']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Email -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="email" class="form-label"><b class="text-uppercase">Email:</b></label>
                                                            <input type="email" name="email" class="form-control border border-dark <?php echo isset($quotation_errors['email']) ? 'is-invalid' : ''; ?>" id="email" placeholder="example@mail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" minlength="1" maxlength="255" title="Insert your email address" autofocus>
                                                            <?php if (isset($quotation_errors['email'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['email']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- County -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="county" class="form-label"><b class="text-uppercase">County:</b></label>
                                                            <select name="county" id="county" class="form-select border border-dark <?php echo isset($registration_errors['county']) ? 'is-invalid' : ''; ?>">
                                                                <option value="" disabled selected>Select County</option>
                                                                <?php if ($counties): ?>
                                                                    <?php foreach ($counties as $county): ?>
                                                                        <option value="<?php echo htmlspecialchars($county['id']); ?>" <?php echo ($_POST['county'] ?? '') == $county['id'] ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($county['county_name']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <option value="" disabled>No counties available</option>
                                                                <?php endif; ?>
                                                            </select>
                                                            <?php if (isset($registration_errors['county'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['county']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- City -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="city" class="form-label"><b class="text-uppercase">City:</b></label>
                                                            <input type="text" name="city" class="form-control border border-dark <?php echo isset($quotation_errors['city']) ? 'is-invalid' : ''; ?>" id="city" placeholder="Enter city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your city" autofocus>
                                                            <?php if (isset($quotation_errors['city'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['city']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Town -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="town" class="form-label"><b class="text-uppercase">Town:</b></label>
                                                            <input type="text" name="town" class="form-control border border-dark <?php echo isset($quotation_errors['town']) ? 'is-invalid' : ''; ?>" id="town" placeholder="Enter town" value="<?php echo htmlspecialchars($_POST['town'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your town" autofocus>
                                                            <?php if (isset($quotation_errors['town'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['town']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- ID Number -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="id_number" class="form-label"><b class="text-uppercase">ID Number/Passport No/Military No:</b></label>
                                                            <input type="text" name="id_number" class="form-control border border-dark <?php echo isset($quotation_errors['id_number']) ? 'is-invalid' : ''; ?>" id="id_number" placeholder="Enter ID Number" value="<?php echo htmlspecialchars($_POST['id_number'] ?? ''); ?>" minlength="1" maxlength="55" title="Enter your ID number" autofocus>
                                                            <?php if (isset($quotation_errors['id_number'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['id_number']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- Date of Birth -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="date_of_birth" class="form-label"><b class="text-uppercase">Date of Birth:</b></label>
                                                            <input type="date" name="date_of_birth" class="form-control border border-dark <?php echo isset($quotation_errors['date_of_birth']) ? 'is-invalid' : ''; ?>" id="date_of_birth" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>" title="Select your date of birth" autofocus>
                                                            <?php if (isset($quotation_errors['date_of_birth'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['date_of_birth']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Age -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="age" class="form-label"><b class="text-uppercase">Age:</b></label>
                                                            <input type="number" name="age" class="form-control border border-dark" id="age" placeholder="Age" value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>" title="Insert your age" autofocus>
                                                        </div>

                                                        <!-- Gender -->
                                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                            <label for="gender" class="form-label"><b class="text-uppercase">Gender:</b></label>
                                                            <select name="gender" id="gender" class="form-select border border-dark <?php echo isset($registration_errors['gender']) ? 'is-invalid' : ''; ?>" autofocus>
                                                                <option value="" disabled selected>Select gender</option>
                                                                <?php if ($genders): ?>
                                                                    <?php foreach ($genders as $gender): ?>
                                                                        <option value="<?php echo htmlspecialchars($gender['id']); ?>" <?php echo ($_POST['gender'] ?? '') == $gender['id'] ? 'selected' : ''; ?>>
                                                                            <?php echo htmlspecialchars($gender['gender_identity']); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <option value="" disabled>No gender identities available</option>
                                                                <?php endif; ?>
                                                            </select>
                                                            <?php if (isset($registration_errors['gender'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['gender']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <!-- Phone NUmber -->
                                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3">
                                                            <label for="phone_number" class="form-label"><b class="text-uppercase">Phone Number:</b></label>
                                                            <input type="phone" name="phone_number" class="form-control border border-dark <?php echo isset($quotation_errors['phone_number']) ? 'is-invalid' : ''; ?>" id="phone_number" placeholder="07xxxxxxxx" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>" title="Insert your phone number" minlength="8" maxlength="15" autofocus>
                                                            <?php if (isset($quotation_errors['phone_number'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['phone_number']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- House Type -->
                                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3">
                                                            <label for="house_type" class="form-label"><b class="text-uppercase">House Type:</b></label>
                                                            <select name="house_type" id="house_type" class="form-select border border-dark <?php echo isset($quotation_errors['house_type']) ? 'is-invalid' : ''; ?>" autofocus>
                                                                <option value="" disabled selected>Select house type</option>
                                                                <!-- Loop through the countries and generate the options dynamically -->
                                                                <?php if ($house_types): ?>
                                                                    <?php foreach ($house_types as $house_type): ?>
                                                                        <option value="<?php echo htmlspecialchars($house_type['id']); ?>">
                                                                            <?php echo htmlspecialchars(str_replace("-", " ", $house_type['type_name'])); ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <option value="" disabled>No house types available</option>
                                                                <?php endif; ?>
                                                            </select>

                                                            <?php if (isset($quotation_errors['house_type'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['house_type']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- No of Beds -->
                                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3">
                                                            <label for="no_of_beds" class="form-label"><b class="text-uppercase">No. of Beds:</b></label>
                                                            <input type="number" name="no_of_beds" class="form-control border border-dark <?php echo isset($quotation_errors['no_of_beds']) ? 'is-invalid' : ''; ?>" id="no_of_beds" placeholder="1" value="<?php echo htmlspecialchars($_POST['no_of_beds'] ?? ''); ?>" min="1" max="5" title="Enter no of beds" autofocus>
                                                            <?php if (isset($quotation_errors['no_of_beds'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['no_of_beds']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- No of Baths -->
                                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3">
                                                            <label for="no_of_baths" class="form-label"><b class="text-uppercase">No. of Baths:</b></label>
                                                            <input type="number" name="no_of_baths" class="form-control border border-dark <?php echo isset($quotation_errors['no_of_baths']) ? 'is-invalid' : ''; ?>" id="no_of_baths" placeholder="1" value="<?php echo htmlspecialchars($_POST['no_of_baths'] ?? ''); ?>" min="1" max="5" title="Enter no of baths" autofocus>
                                                            <?php if (isset($quotation_errors['no_of_baths'])): ?>
                                                                <span class="invalid-feedback alert alert-warning" role="alert">
                                                                    <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['no_of_baths']; ?></strong>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <!-- Submit -->
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <button type="submit" name="submit_quotation" class="btn btn-success text-uppercase text-white float-end rounded-pill">Submit</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <footer class="footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                    <h2>Location</h2>
                                    <p>View Park Towers, Utalii Lane P.O. Box 5941-00100</p>
                                </div>

                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                    <h2>Follow Us</h2>

                                    <div class="social-icons">
                                        <a class="text-decoration-none" href="https://www.facebook.com/omohhomeshousing" target="_blank"><i class="fab fa-facebook-f"></i>Facebook</a>

                                        <br>

                                        <a class="text-decoration-none" href="https://www.instagram.com/omoh_homes/" target="_blank"><i class="fab fa-instagram"></i>Instagram</a>

                                        <br>

                                        <a class="text-decoration-none" href="https://www........com" target="_blank"><i class="fab fa-linkedin-in"></i>Linkedin</a>

                                        <br>

                                        <a class="text-decoration-none" href="https://x.com/Omoh_Homes" target="_blank"><i class="fa-brands fa-x-twitter"></i>Twitter (X)</a>

                                        <br>

                                        <a class="text-decoration-none" href="https://www.youtube.com/@OmohHomes" target="_blank"><i class="fab fa-youtube"></i>Youtube</a>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                    <h2>Contact Us</h2>

                                    <span>Email:</span><a class="text-decoration-none ms-1" href="mailto:info@omohhomes.com">info@omohhomes.com</a>

                                    <br>

                                    <span>Phone:</span><a class="text-decoration-none ms-1" href="tel:+254 716 700 762">+254 716 700 762</a>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                    <p class="text-center">&copy; <?php echo date("Y"); ?> OmohHomes. All rights reserved.</p>
                                </div>
                            </div>
                        </footer>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            const $ageInput = $('#age');
            const $dobInput = $('#date_of_birth');

            // Function to calculate age based on DOB
            function calculateAge(dob) {
                const today = new Date();
                const birthDate = new Date(dob);
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age;
            }

            // Function to calculate date of birth based on age
            function calculateDob(age) {
                const today = new Date();
                const birthYear = today.getFullYear() - age;
                const birthDate = new Date(today.setFullYear(birthYear));
                // Ensure DOB is set to January 1st of the birth year for consistency
                birthDate.setMonth(0);
                birthDate.setDate(1);
                return birthDate.toISOString().split('T')[0]; // Return date in YYYY-MM-DD format
            }

            // Event listener for Date of Birth field
            $dobInput.on('change', function () {
                const dob = $(this).val();
                if (dob) {
                    const calculatedAge = calculateAge(dob);
                    $ageInput.val(calculatedAge);
                    $ageInput.prop('readonly', true); // Set age as readonly
                }
            });

            // Event listener for Age field
            $ageInput.on('input', function () {
                const age = $(this).val();
                if (age) {
                    const calculatedDob = calculateDob(age);
                    $dobInput.val(calculatedDob);
                    // Note: No readonly on $dobInput as per your request
                }
            });

            $("#registrationForm #phone_number").on("change", function() {
                let phoneNumber = $(this).val(); // Get the value of the input field

                if (phoneNumber.startsWith("0")) {
                    // If the number starts with 0, replace it with 254
                    phoneNumber = "254" + phoneNumber.substring(1);
                }

                // Set the updated value back to the input field
                $(this).val(phoneNumber);
            });

            $("#registrationForm #payment_plan").on("change", function() {
                if ($("#payment_plan").val() !== "3") {
                    // Make mortgage_plan readonly and set its value to 1
                    $("#mortgage_plan").attr("readonly", true).val("1");
                } else {
                    // Remove readonly if payment_plan is 3
                    $("#mortgage_plan").removeAttr("readonly");
                }
            });

            // Event listener for "Register Now" button
            $('#registrationButton').click(function (e) {
                e.preventDefault(); // Prevent default link behavior
                // Show registration form and hide quotation form
                $('#registrationCard').show();
                $('#quotationCard').hide();
                // Scroll to registration form
                $('html, body').animate({
                    scrollTop: $('#registrationCard').offset().top
                }, 500); // Smooth scroll with 500ms duration
            });

            // Event listener for "Request Quote" button
            $('#quotationButton').click(function (e) {
                e.preventDefault(); // Prevent default link behavior
                // Show quotation form and hide registration form
                $('#quotationCard').show();
                $('#registrationCard').hide();
                // Scroll to quotation form
                $('html, body').animate({
                    scrollTop: $('#quotationCard').offset().top
                }, 500); // Smooth scroll with 500ms duration
            });

            // Check if there are any errors present in the form when the page loads
            if ($('#registrationCard').find('.is-invalid').length > 0) {
                // If there are errors in the registration form, show it and hide the quotation form
                $('#registrationCard').show();
                $('#quotationCard').hide();
                $('html, body').animate({
                    scrollTop: $('#registrationCard').offset().top
                }, 500); // Smooth scroll to the registration form
            } else if ($('#quotationCard').find('.is-invalid').length > 0) {
                // If there are errors in the quotation form, show it and hide the registration form
                $('#quotationCard').show();
                $('#registrationCard').hide();
                $('html, body').animate({
                    scrollTop: $('#quotationCard').offset().top
                }, 500); // Smooth scroll to the quotation form
            }
        });
    </script>
</body>

</html>
