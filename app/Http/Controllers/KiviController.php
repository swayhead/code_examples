<?php

namespace App\Http\Controllers;

use App\kivi\KiviException;
use App\kivi\KiviManager;
use App\Models\kivi\KiviModeratorLink;
use App\Models\kivi\KVAdmin;
use App\Models\kivi\KVEvent;
use BigBlueButton\Parameters\JoinMeetingParameters;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Mpdf\Mpdf;


class KiviController extends Controller
{
    public function moderationStart($hashOrEventId, $eventId = null)
    {
        // Administrator/Moderator
        $kva = null;
        // Event
        $kve = null;
        // Manager for BigBlueButton connections
        $kivi = new KiviManager();

        try {
            // User may come as a logged in KurVe admin or via a universal link

            // as a logged in admin
            if (is_numeric($hashOrEventId)) {
                // For logging
                $linkType = 1;
                $eventId = $hashOrEventId;
                $kve = KVEvent::findOrFail($eventId);
                if (!Auth::check()) {
                    $kivi->logError('kivi100m', "ID: $hashOrEventId");
                }
                // get the admin out of the logged user (one-to-one)
                $kva = KVAdmin::findOrFail(Auth::user()->address_id);
            }

            // universal link
            if (preg_match('#^[a-f0-9]{32}$#', $hashOrEventId)) {
                // for logging
                $linkType = 2;
                // find event
                $kve = KVEvent::findOrFail($eventId);
                // find admin
                $kva = KVAdmin::findOrFail($kve->provider_id);

                // find link by hash
                $link = KiviModeratorLink::where('hash', '=', $hashOrEventId)->first();

                // false hash 
                if (is_null($link)) {
                    throw new KiviException('kivi105m', ["Hash: $hashOrEventId"]);
                }

                // link not corresponding to event
                if ($link->event_id != $eventId) {
                    throw new KiviException('kivi106m', ["Hash: $hashOrEventId - KVE: $eventId"]);
                }

                // link expired
                if ($link->isExpired()) {
                    throw new KiviException('kivi107m', ["Hash: $hashOrEventId ", 'Der Link ist am ' . $link->expiry_date->format('d.m.Y') . ' abgelaufen.']);
                }
            }

            // no rights for this module
            if (!$kva->hasModule('kivi')) {
                throw new KiviException('kivi101m', ["Link type: $linkType - KVA: {$kva->id}"]);
            }

            // admin is not valid (expired or not within policies)
            if (!$kva->isValidMember()) {
                throw new KiviException('kivi102m', ["Link type: $linkType - KVA: {$kva->id}", "Days diff: " . $kva->paid_till->diffInDays(now())]);
            }

            // event does not belong to this admin
            if (!$kva->events->contains($kve)) {
                throw new KiviException('kivi104m', ["Link type: $linkType - KVA: {$kva->id} - KVE: $eventId"]);
            }

            $meetingId = "kve_{$kve->id}_{$kve->kv_id}";

            // Default slide to show in the BBB session
            $pdf = new Mpdf([
                'orientation' => 'L',
                'default_font' => 'helvetica',
                'default_font_size' => 10,
                'margin_left' => 20,
                'margin_right' => 20,
            ]);

            $pdf->AddPage();

            $css = 'body {color: #696969;}';
            $pdf->writeHTML($css, 1);
            $html = "<h1>Willkommen</h1>";
            $html .= '<div style="position: fixed; right: 0mm; top: 0mm;"><h2>' . $kve->header_long . '</h2></div>';
            $html .= '<div style="position: fixed; right: 0mm; bottom: 0mm;"><img src="https://www.kidsgo.de/app/images/kivi_logo.png" width="75"></div>';
            $pdf->writeHTML($html);

            // create BBB session
            $kivi->createMeeting($meetingId, $kve->header_long, $pdf->Output('', 'S'), ['kve' => $kve->id, 'kva' => $kve->provider_id]);

            $joinParams = new JoinMeetingParameters($meetingId, 'Kursleiter', env('BBB_MODERATOR_PWD'));
            $joinParams->setRedirect(true);
            $joinParams->setJoinViaHtml5(true);

            // redirect and start BBB session
            return Redirect::to($kivi->getBBB()->getJoinMeetingURL($joinParams));
        } catch (Exception $e) {
            $message = method_exists($e, 'getKiviMessage') ? $e->{'getKiviMessage'}() : $e->getMessage();
            return $kivi->renderKiviPage([$message]);
        }
    }

    public function participantJoin($hashOrEventId, $eventId = null)
    {
        // ...
    }
}
