<?php

namespace INTERSECT\SurveyDirector;

use ExternalModules\AbstractExternalModule;
use REDCap;
use Piping;

class SurveyDirector extends \ExternalModules\AbstractExternalModule {

    function redcap_survey_complete($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance)
    /* function redcap_survey_page($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) // For debugging */
    {
        $instruments = $this -> getProjectSetting('instrument');

        for ($i = 0; $i < count($instruments); $i++){ // Loop over each new configuration
            //Retrieve module configuration settings
            $instrument_select = $this -> getProjectSetting('instrument_select')[$i];
            // If we're not in this instrument, skip it
            if ($instrument != $instrument_select) continue;
            $event_select = $this -> getProjectSetting('event_select')[$i];
            // If we're not in this event, skip it
            if (!is_null($event_select) && $event_id != $event_select) continue;
            $condition = $this -> getProjectSetting('condition')[$i];
            // Parse each condition
            for ($c = 0; $c < count($condition); $c++) {
                $logic = $this -> getProjectSetting('logic')[$i][$c];
                // If the logical condition is met, go to the target
                if (is_null($logic) || REDCap::evaluateLogic($logic, $project_id, $record, $event_id, $repeat_instance))
                {
                    $target = $this -> getProjectSetting('target')[$i][$c];
                    $target = Piping::replaceVariablesInLabel(
                        $target, // $label='', 
                        $record, // $record=null, 
                        $event_id, // $event_id=null, 
                        $repeat_instance, // $instance=1, 
                        array(), // $record_data=array(),
                        false, // $replaceWithUnderlineIfMissing=true, 
                        $project_id, // $project_id=null, 
                        false // $wrapValueInSpan=true
                    );
                    header('Location: '.$target);
                    $this->exitAfterHook();
                    // Echo out target and logic to javascript console for troubleshooting
                    echo "<script type=\"text/javascript\">console.log(".$target.");console.log(".$logic.");";
                    // Need to exit here, the first time a condition is evaluated as true. Else it will be the last condition that triggers the redirect.
                    return;
                }
            }
        }
    }
}
