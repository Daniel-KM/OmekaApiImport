<?php

class ApiImport_ResponseAdapter_Omeka_CollectionAdapter extends ApiImport_ResponseAdapter_AbstractRecordAdapter
{
    protected $recordType = 'Collection';

    /**
     * Insert the collection into the database
     *
     */
    public function import()
    {
        debug(print_r($this->responseData, true));
        debug('start collection import');
        $collectionMetadata = $this->collectionMetadata();
        debug(print_r($collectionMetadata, true));
        $elementTexts = $this->elementTexts();
        debug(print_r($elementTexts, true));
        if($this->record && $this->record->exists()) {
            debug('updating');
            $collectionMetadata['overwriteElementTexts'] = true;
            update_collection($this->record, $collectionMetadata, $elementTexts);
            debug('done update');
        } else {
            try {
                debug('inserting');
                $this->record = insert_collection($collectionMetadata, $elementTexts);
                debug('done insert');
            } catch(Exception $e) {
                _log($e);
            }
            debug('before add map');
            $this->addOmekaApiImportRecordIdMap();
        }
        debug('done');
    }

    /**
     * Find the external Id for the collection in the response
     * @see ApiImport_ResponseAdapter_RecordAdapterInterface::externalId()
     */
    public function externalId()
    {
        return $this->responseData['id'];
    }

    /**
     * Put together the collection metadata for insertion into database
     *
     * @see insert_collection()
     * @see update_collection()
     * @return array $metadata formatted to be the metadata insert param to insert/update_collection
     */
    protected function collectionMetadata()
    {
        $metadata = array();
        $metadata['public'] = $this->responseData['public'];
        $metadata['featured'] = $this->responseData['featured'];
        return $metadata;
    }

    protected function elementTexts($responseData = null)
    {
        $elementTexts = array();
        if(!$responseData) {
            $responseData = $this->responseData;
        }

        foreach($responseData['element_texts'] as $elTextData) {
            $elName = $elTextData['element']['name'];
            $elSet = $elTextData['element_set']['name'];
            $elTextInsertArray = array('text' => $elTextData['text'],
                                       'html' => $elTextData['html']
                                       );
            $elementTexts[$elSet][$elName][] = $elTextInsertArray;

        }
        return $elementTexts;
    }

}