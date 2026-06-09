<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @category    string
 * @copyright   Konrad Ebel <konrad.ebel@oncampus.de>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activityfilter:filter_activities'] = 'Darf Inhaltstypen mithilfe von KI durchsuchen';
$string['activityfilter:get_max_content_item_occurrence'] = 'Kann die Verwendungszahl des meistgenutzten Inhaltstypen abrufen';

$string['error:ai_call'] = 'Die KI-Anfrage ist fehlgeschlagen. Bitte versuche es später erneut.';

$string['hintcolumn'] = 'Beschreibung';
$string['modal_title'] = 'Vorgeschlagene Aktivitäten';
$string['noresults'] = 'Noch keine Ergebnisse – bitte geben Sie zuerst eine Anfrage ein.';

$string['occurences:moderately'] = 'moderat';
$string['occurences:often'] = 'oft';
$string['occurences:rare'] = 'selten';
$string['occurences:very_frequent'] = 'sehr häufig';
$string['occurences:very_rare'] = 'sehr selten';

$string['open_activityfilter'] = 'KI-Aktivitätensuche';
$string['pluginname'] = 'Aktivitäten-Filter';
$string['popularitycolumn'] = 'Häufigkeit';
$string['privacy:null_reason'] = 'Plugin speichert keine Daten über Nutzer, schickt aber Daten an den KI-Provider';
$string['promptdesc'] = 'Nach welcher Art von Aktivität suchen Sie?';
$string['purposeplacedescription_singleprompt'] = 'Wird genutzt, um die KI nach Aktivitätsempfehlungen auf Basis der Benutzereingabe zu befragen.';
$string['reasoncolumn'] = 'Begründung';
$string['search'] = 'Suchen';

$string['settings:backend'] = 'KI-Backend';
$string['settings:backend_coreai'] = 'Moodle Core KI-Subsystem';
$string['settings:backend_desc'] = 'Wählen Sie das KI-Backend für die Aktivitätenfilterung aus.';
$string['settings:backend_dummy_mode'] = 'Dummy-Modus';
$string['settings:backend_localaimanager'] = 'local_ai_manager (mebis)';
$string['settings:disabled'] = 'Deaktiviert';
$string['settings:plugin_ai_hint'] = 'Leittext, der der KI erklärt, welche Möglichkeiten dieses Plugin bietet.';
$string['settings:systemprompt'] = 'System-Prompt';
$string['settings:systemprompt_desc'] = 'Legt den System-Prompt fest, der das Verhalten und die Antworten des KI-Modells steuert.';
$string['settings:use_default'] = 'Verwende Standard ({$a})';
$string['settings:use_default_desc'] = 'Verwende die Standardeinstellung für {$a}';
