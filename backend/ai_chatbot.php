<?php
// Extend session duration to 1 year (365 days)
$lifetime = 365 * 24 * 60 * 60; // 1 year in seconds
session_set_cookie_params($lifetime);
ini_set('session.gc_maxlifetime', $lifetime);
session_start();

header('Content-Type: application/json');

// --- Setup MySQL connection using PDO ---
$host = "localhost";
$dbname = "library";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// --- Ensure user identity is set ---
// We assume that 'user_id' is stored in session upon login.
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}
$userId = $_SESSION['user_id'];

// Set a default first name from session (fallback to 'Guest' if not set)
$firstname = isset($_SESSION['firstname']) ? htmlspecialchars($_SESSION['firstname']) : 'Guest';

// --- Initialize or Load Conversation History from Database ---
if (!isset($_SESSION['chatHistories'])) {
    $_SESSION['chatHistories'] = [];
}
if (!isset($_SESSION['chatHistories'][$userId])) {
    $_SESSION['chatHistories'][$userId] = [];
    try {
        $stmt = $pdo->prepare("SELECT message, reply, timestamp FROM chat_history WHERE user_id = ? ORDER BY timestamp ASC");
        $stmt->execute([$userId]);
        $chatHistoryFromDB = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($chatHistoryFromDB) {
            $_SESSION['chatHistories'][$userId] = $chatHistoryFromDB;
        }
    } catch (PDOException $e) {
        error_log("Error loading chat history: " . $e->getMessage());
    }
}

// --- Receive user message from frontend ---
$data = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($data['message'] ?? '');
$cleanMessage = htmlspecialchars(strip_tags($userMessage));

// --- Fuzzy matching function for phrases ---
function isSimilarPhrase($input, $phrases, $thresholdFactor = 0.4) {
    $input = strtolower($input);
    foreach ($phrases as $phrase) {
        $phrase = strtolower($phrase);
        $threshold = max(1, floor(strlen($phrase) * $thresholdFactor));
        if (levenshtein($input, $phrase) <= $threshold) {
            return true;
        }
    }
    return false;
}

// --- Helper function: Check if text is mostly English ---
function isMostlyEnglish($text) {
    // Remove non-letter characters for a basic ratio.
    $lettersOnly = preg_replace('/[^a-zA-Z]/', '', $text);
    if (strlen($text) === 0) return true; // Treat empty text as English.
    return (strlen($lettersOnly) >= strlen($text) / 2);
}

// --- Predefined responses ---
// All arrays are in lowercase for consistent matching.
$englishGreetings      = ['hi', 'hello', 'good morning', 'good afternoon', 'good evening'];
$tagalogGreetings      = ['kamusta', 'magandang umaga', 'magandang tanghali', 'magandang hapon', 'magandang gabi'];

// Confirmation messages (English & Tagalog)
$confirmationMessages  = ['sige', 'okay', 'ok', 'ge', 'sge', 'omki', 'hmm sige', 'sure', 'alright'];

// Gratitude messages (English & Tagalog)
$gratitudeMessages     = ['thank you', 'thanks', 'thankyou', 'salamat', 'salamat po', 'maraming salamat'];

// --- Determine chatbot response based on fuzzy matching ---
if (isSimilarPhrase($cleanMessage, $englishGreetings)) {
    $response = "Hello $firstname! Welcome to Trimex Library. How can I assist you today?";
} elseif (isSimilarPhrase($cleanMessage, $tagalogGreetings)) {
    $response = "Kamusta $firstname! Maligayang pagdating sa Trimex Library. Paano kita matutulungan ngayon?";
} elseif (isSimilarPhrase($cleanMessage, $confirmationMessages)) {
    $response = "Maraming salamat sa iyong pag-unawa!";
} elseif (isSimilarPhrase($cleanMessage, $gratitudeMessages)) {
    $response = (stripos($cleanMessage, 'salamat') !== false) ? "Walang anuman!" : "You're welcome!";
} elseif (stripos($cleanMessage, 'trimex') !== false) {
    if (stripos($cleanMessage, 'salamat') !== false || stripos($cleanMessage, 'kamusta') !== false) {
        $response = "Alam lamang po, ang Trimex Library ay nagbibigay ng malawak na resources at tools para sa inyong pangangailangan. Kung may karagdagan pa kayong katanungan, ipaalam lamang po.";
    } else {
        $response = "Just so you know, Trimex Library offers a wide range of resources and tools to meet your needs. If you have any further questions, please let me know.";
    }
} else {
    // --- Check if the message contains book-related keywords ---
    $bookKeywords = [
        'tourism', 'ccs', 'bm', 'bussiness', 'business management', 'accountancy', 'accountant',
        'engineering', 'engineer', 'computers', 'computer', 'ict', 'psychology', 'psych', 'crim',
        'criminology', 'real estate', 'social work', 'medical', 'meds', 'profed', 'shs', 'senior high',
        'senior highschool', 'textbook', 'gened', 'philosophy', 'religion', 'social science', 'language',
        'pure science', 'science', 'technology', 'arts', 'the arts', 'literature', 'history', 'geography',
        'reference', 'filipiniana', 'foreign', 'fiction', 'biography', 'thesis','computer','computers','technology','ict','it','doctor',
        'Police','Pulis','sundalo','teacher', 
    ];

    $matchedKeyword = false;
    foreach ($bookKeywords as $keyword) {
        if (stripos($cleanMessage, $keyword) !== false) {
            $matchedKeyword = true;
            break;
        }
    }
    if ($matchedKeyword) {
        if (isMostlyEnglish($cleanMessage)) {
            $response = "Yes, we have that. Just type it in the search bar!";
        } else {
            $response = "Oo naman, meron kame nyan itype mo lang sa iyong search bar!";
        }
    } else {
        if (isMostlyEnglish($cleanMessage)) {
            $response = "Sorry, I did not understand your question. Could you please repeat it?";
        } else {
            $response = "Pasensya na, hindi ako nakasabot ng iyong tanong. Puwede mo bang ulitin ang iyong katanungan?";
        }
    }
}

// --- Append current exchange to session history ---
$currentExchange = [
    'message'   => $cleanMessage,
    'reply'     => $response,
    'timestamp' => time()
];
$_SESSION['chatHistories'][$userId][] = $currentExchange;

// --- Store conversation in the database ---
try {
    $stmt = $pdo->prepare("INSERT INTO chat_history (user_id, message, reply, timestamp) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $cleanMessage, $response, time()]);
} catch (PDOException $e) {
    error_log("Chat DB insert error: " . $e->getMessage());
}

// --- Return JSON response with updated conversation history ---
echo json_encode([
    'response'    => $response,
    'chatHistory' => $_SESSION['chatHistories'][$userId]
]);

session_write_close();
?>
