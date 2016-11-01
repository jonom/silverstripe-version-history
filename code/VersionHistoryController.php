<?php

class VersionHistory_Controller extends Controller
{

    private static $url_handlers = array(
        '$Action/$Model/$ID/$VersionID/$OtherVersionID' => 'handleAction'
    );

    private static $allowed_actions = array(
        'compare'
    );

    /**
     * Return output suitable for an ajax request.
     */
    public function compare()
    {
        $id = (int) $this->getRequest()->param('ID');
        $model = $this->getRequest()->param('Model');
        $versionID = $this->getRequest()->param('VersionID');
        $otherVersionID = $this->getRequest()->param('OtherVersionID');

        if (!$id) {
            $this->httpError(400, 'No ID specified');

            return false;
        }

        $record = $model::get()->byID($id);

        if (!$record) {
            $this->httpError(404);

            return false;
        }

        if (!$record->canView()) {
            return Security::permissionFailure($this);
        }

        return $record->VersionComparisonSummary($versionID, $otherVersionID);
    }
}
