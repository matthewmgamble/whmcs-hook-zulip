<?php
/**
 * Zulip notification hook
 */
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
function get_client_name($clientid)
{
    $config		= include("zulip-config.php");
    $adminuser          = $config['adminuser'];
    $client             = "";
    $command            = "getclientsdetails";
    $values["clientid"] = $clientid;
    $values["pid"]      = $pid;
    $results            = localAPI($command, $values, $adminuser);
    $parser             = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $results, $values, $tags);
    xml_parser_free($parser);
    $data = array();
    if ($results["result"] == "success") {
        $client  = $results["firstname"] . " " . $results["lastname"];
        $client  = trim($client);
        $company = $results["companyname"];
        if ($company != "") {
            $client .= " (" . $company . ")";
        }
    } else {
        $client = "Error";
    }
    return $client;
}
function zulip_post($text, $subject)
{
    $config  = include("zulip-config.php");
    $url     = $config['hook_url'];
    $text    = htmlspecialchars_decode($text, ENT_QUOTES | ENT_NOQUOTES);
    $payload = array(
        "content" => urlencode(htmlspecialchars($text)),
        "type" => urlencode("stream"),
        "subject" => urlencode($subject),
        "to" => urlencode($config["stream"])
    );
    foreach ($payload as $key => $value) {
        $fields_string .= $key . '=' . $value . '&';
    }
    rtrim($fields_string, '&');
    logActivity("Send zulip notification:" . $text);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, $config['botuser'] . ":" . $config['api_key']);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (!$result = curl_exec($ch)) {
        logActivity("Error posting to zulip: " + curl_error($ch));
    }
}
function hook_zulip_ticketopen($vars)
{
    $ticketid = $vars['ticketid'];
    $userid   = $vars['userid'];
    $deptid   = $vars['deptid'];
    $deptname = $vars['deptname'];
    $subject  = $vars['subject'];
    $message  = $vars['message'];
    $priority = $vars['priority'];
    $name     = get_client_name($userid);
    $text     = "[ID: " . $ticketid . "] " . $subject . "\r\n";
    $text .= "User: " . $name . "\r\n";
    $text .= "Department: " . $deptname . "\r\n\r\n";
    //$text .= "Priority: ".$priority."\r\n";
    $text .= $message . "\r\n";
    $zulip_subject = "[" . $ticketid . "]" . " - " . $subject;
    zulip_post($text, $zulip_subject);
}
function hook_zulip_ticketuserreply($vars)
{
    $ticketid = $vars['ticketid'];
    $userid   = $vars['userid'];
    $deptid   = $vars['deptid'];
    $deptname = $vars['deptname'];
    $subject  = $vars['subject'];
    $message  = $vars['message'];
    $priority = $vars['priority'];
    $name     = get_client_name($userid);
    $text     = "Customer Reply\r\n";
    $text .= "User: " . $name . "\r\n";
    $text .= "Department: " . $deptname . "\r\n\r\n";
    //$text .= "Priority: ".$priority."\r\n";
    $text .= $message . "\r\n";
    $zulip_subject = "[" . $ticketid . "]" . " - " . $subject;
    zulip_post($text, $zulip_subject);
}
function hook_zulip_ticketadminreply($vars)
{
    $ticketid = $vars['ticketid'];
    $admin    = $vars['admin'];
    $deptid   = $vars['deptid'];
    $deptname = $vars['deptname'];
    $subject  = $vars['subject'];
    $message  = $vars['message'];
    $priority = $vars['priority'];
    $text     = "Staff Response\r\n";
    $text .= "Admin: " . $admin . "\r\n";
    $text .= "Department: " . $deptname . "\r\n\r\n";
    //$text .= "Priority: ".$priority."\r\n";
    $text .= $message . "\r\n";
    $zulip_subject = "[" . $ticketid . "]" . " - " . $subject;
    zulip_post($text, $zulip_subject);
}
add_hook("TicketOpen", 1, "hook_zulip_ticketopen");
add_hook("TicketUserReply", 1, "hook_zulip_ticketuserreply");
add_hook("TicketAdminReply", 1, "hook_zulip_ticketadminreply");
