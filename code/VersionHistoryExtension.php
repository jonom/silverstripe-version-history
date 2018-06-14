<?php

class VersionHistoryExtension extends DataExtension
{
    public function updateCMSFields(FieldList $fields)
    {
        // Only add history field if history exists
        if ($this->owner->ID && $vFields = $this->owner->getVersionsFormFields()) {
            // URL for ajax request
            $urlBase = Director::absoluteURL('cms-version-history/compare/'.$this->owner->ClassName.'/'.$this->owner->ID.'/');
            $fields->findOrMakeTab('Root.VersionHistory', 'History');
            $fields->addFieldToTab('Root.VersionHistory', LiteralField::create('VersionsHistoryMenu',
                "<div id=\"VersionHistoryMenu\" class=\"cms-content-tools\" data-url-base=\"$urlBase\">"
                .$vFields->forTemplate()
                .'</div>'
            ));
            $fields->addFieldToTab('Root.VersionHistory', LiteralField::create('VersionComparisonSummary',
                '<div id="VersionComparisonSummary">'
                .$this->owner->VersionComparisonSummary()
                .'</div>'
            ));
            Requirements::css('version-history/css/version-history.css');
            Requirements::javascript('version-history/javascript/VersionHistory.js');
        }
    }

    /**
     * Return an XML string description of a field value or related has_one record.
     *
     * @param Versioned_Version $record
     * @param array $fieldInfo
     * @return string
     */
    public function getVersionFieldValue($record, $fieldInfo)
    {
        if ($fieldInfo['Type'] == 'HasOne') {
            $hasOne = $record->{$fieldInfo['Name']}();
            return Convert::RAW2XML($hasOne->getTitle());
        } else {
            $dbField = $record->dbObject($fieldInfo['Name']);
            if (method_exists($dbField, 'Nice')) {
                return $dbField->Nice();
            }
            return Convert::RAW2XML($dbField->Value);
        }
    }

    /**
     * Display a pre-rendered read only set of fields summarising a specific version.
     * Includes a comparison with the previous version or arbitrary version if specified.
     * If no version ID is specified the latest version is used.
     *
     * @param int $versionID      (default: null)
     * @param int $otherVersionID (default: null)
     */
    public function VersionComparisonSummary($versionID = null, $otherVersionID = null)
    {
        if ($versionID && $otherVersionID) {
            // Compare two specified versions
            if ($versionID > $otherVersionID) {
                $toVersion = $versionID;
                $fromVersion = $otherVersionID;
            } else {
                $toVersion = $otherVersionID;
                $fromVersion = $versionID;
            }
            $fromRecord = Versioned::get_version($this->owner->class, $this->owner->ID, $fromVersion);
            $toRecord = Versioned::get_version($this->owner->class, $this->owner->ID, $toVersion);
        } else {
            // Compare specified version with previous. Fallback to latest version if none specified.
            $filter = '';
            if ($versionID) {
                $filter = "\"Version\" <= '$versionID'";
            }
            $versions = $this->owner->allVersions($filter, '', 2);
            if ($versions->count() === 0) {
                return false;
            }
            $toRecord = $versions->first();
            $fromRecord = ($versions->count() === 1) ? null : $versions->last();
        }

        if (!$toRecord) {
            return false;
        }

        $fields = FieldList::create();

        // Generate a list of fields and information about them
        $fieldNames = array();
        foreach ($this->owner->db() as $fieldName => $fieldType) {
            $fieldNames[$fieldName] = array(
                'FieldName' => $fieldName,
                'Name' => $fieldName,
                'Type' => 'Field',
            );
        }
        foreach ($this->owner->hasOne() as $has1) {
            $fieldNames[$has1] = array(
                'FieldName' => $has1.'ID',
                'Name' => $has1,
                'Type' => 'HasOne',
            );
        }
        unset($fieldNames['Version']);

        // Compare values between records and make them look nice
        foreach ($fieldNames as $fieldName => $fieldInfo) {
            $compareValue = ($fromRecord && $toRecord->$fieldInfo['FieldName'] !== $fromRecord->$fieldInfo['FieldName'])
                ? Diff::compareHTML($this->getVersionFieldValue($fromRecord, $fieldInfo), $this->getVersionFieldValue($toRecord, $fieldInfo))
                : $this->getVersionFieldValue($toRecord, $fieldInfo);
            $field = ReadonlyField::create("VersionHistory$fieldName", $this->owner->fieldLabel($fieldName), $compareValue);
            $field->dontEscape = true;
            $fields->push($field);
        }

        return $fields->forTemplate();
    }

    /**
     * Version select form. Main interface between selecting versions to view
     * and comparing multiple versions.
     *
     * @return FieldList
     */
    public function getVersionsFormFields()
    {
        $versions = $this->owner->allVersions();
        if (!$versions->count()) {
            return false;
        }

        $versions->first()->Active = true;

        $vd = new ViewableData();

        $versionsHtml = $vd->customise(array(
            'Versions' => $versions,
        ))->renderWith('VersionHistory_versions');

        $fields = new FieldList(
            new CheckboxField(
                'CompareMode',
                _t('CMSPageHistoryController.COMPAREMODE', 'Compare mode (select two)')
            ),
            new LiteralField('VersionsHtml', $versionsHtml)
        );

        return $fields;
    }
}
