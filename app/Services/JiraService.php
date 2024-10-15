<?php

namespace App\Services;

use GuzzleHttp\Client;

class JiraService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.jira.base_url'),
            'auth' => [config('services.jira.email'), config('services.jira.token')],
        ]);
    }

    // Fetch tickets from the last sprint
    public function getTicketsFromSprint($sprintId)
    {
        // $response = $this->client->get("/rest/agile/1.0/sprint/{$sprintId}/issue");
        $response = $this->client->get("/rest/agile/1.0/sprint/{$sprintId}/issue", [
            'query' => [
                'fields' => 'summary,description,resolution,issuetype,status,labels,customfield_10700,customfield_12743',  // Only get the title (summary) and description
            ]
        ]);
        return json_decode($response->getBody(), true)['issues'];
    }

    public function processTickets($tickets)
    {
        $processedTickets = [];
        $delievered = [];
        $inProgress = [];

        foreach ($tickets as $ticket) {
            // Check for the placeholder in the summary
            $delivered = strpos($ticket['fields']['summary'], '[PLACEHOLDER]') === false; // Replace with your actual placeholder logic


            $ticketInfo = [
                // 'key' => $ticket['key'],
                'delivered' => $delivered ? 'Yes' : 'No',
                'codebase' => $ticket['fields']['customfield_10700']['value'] ?? null, // Custom field value
                'issue_type' => $ticket['fields']['issuetype']['name'] ?? '', // Issue type name
                'summary' => $ticket['fields']['summary'] ?? '', // Summary or title
                // 'description' => $ticket['fields']['description'] ?? '', // Ticket description
            ];

            $codebase = $ticketInfo['codebase'] ?? 'miscellaneous';
            if ($delivered) {
                $delievered[$codebase][] = $ticketInfo;
            } else {
                $ticketInfo['summary'] = str_replace('[PLACEHOLDER]', '', $ticketInfo['summary']);
                $inProgress[$codebase][] = $ticketInfo;
            }
        }

        // Move 'miscellaneous' to the end for delivered tickets
        if (isset($delievered['miscellaneous'])) {
            $miscellaneousDelivered = $delievered['miscellaneous'];
            unset($delievered['miscellaneous']);
            $delievered['miscellaneous'] = $miscellaneousDelivered;
        }

        // Move 'miscellaneous' to the end for in-progress tickets
        if (isset($inProgress['miscellaneous'])) {
            $miscellaneousInProgress = $inProgress['miscellaneous'];
            unset($inProgress['miscellaneous']);
            $inProgress['miscellaneous'] = $miscellaneousInProgress;
        }

        $processedTickets['delivered'] = $delievered;
        $processedTickets['in_progress'] = $inProgress;

        return $processedTickets;
    }
}
