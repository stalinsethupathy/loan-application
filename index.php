<?php
// Extract endpoint and application number from the request URI
$getReqData = $_SERVER['REQUEST_URI'];
$uriParts = explode('/', $getReqData);
$endpoint = $uriParts[2] ?? null;
$applicationNumber = $uriParts[4] ?? null;

// Array to store loan application data temporarily
$loanApplications = [];

/**
 * Function to validate loan application based on application number
 *
 * @param string $applicationNumber Application number to validate
 * @return array|null Loan application data if found, else null
 */
function validateLoanApplication($applicationNumber): ?array
{
    // Load loan application data from JSON file
    $jsonData = file_get_contents('file.json');
    $loanData = json_decode($jsonData, true);

    // Search for the application number in loan data
    $foundLoanData = null;
    foreach ($loanData['lanetakere'] as $lanetaker) {
        if ($lanetaker['fnr'] === $applicationNumber) {
            $foundLoanData = [
                'navn' => $lanetaker['navn'],
                'lanebelop' => $loanData['lanebelop'],
                'behov' => $loanData['behov']
            ];
            break;
        }
    }
    return $foundLoanData;
}

// Handle GET requests for retrieving loan application status
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $endpoint === 'api' && $applicationNumber) {
    // Validate and retrieve loan application data
    $foundLoanData = validateLoanApplication($applicationNumber);

    // Send response based on whether the application number was found or not
    if ($foundLoanData !== null) {
        http_response_code(200);
        echo json_encode(['status' => 'Found', 'loanData' => $foundLoanData]);
    } else {
        http_response_code(200);
        echo json_encode(['status' => 'Not Found']);
    }
}

// Handle POST requests for submitting new loan applications
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $endpoint === 'api' && empty($applicationNumber)) {
    $data = json_decode(file_get_contents('php://input'), true);
    // Validate and process the loan application data as needed

    // Hash the JSON data before storing it
    $hashedData = md5(json_encode($data));
    $data['hash'] = $hashedData;

    // Store the application data in $loanApplications array
    // Generate a unique application number and store it with the application data
    $applicationNumber = uniqid();
    $data['applicationNumber'] = $applicationNumber;
    $loanApplications[$applicationNumber] = $data;

    // Send response with application number
    http_response_code(200);
    echo json_encode(['applicationNumber' => $applicationNumber]);
}

// Handle invalid endpoints
else {
    // Send response for invalid endpoint
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}

