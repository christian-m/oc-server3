<?php

namespace Oc\FieldNotes\Import;

use DateTime;
use DateTimeZone;
use Oc\FieldNotes\Enum\LogType;
use Oc\FieldNotes\Import\Context\ImportContext;
use Oc\FieldNotes\Persistence\FieldNoteEntity;
use Oc\FieldNotes\Persistence\FieldNoteService;
use Oc\FieldNotes\Struct\FieldNote;
use Oc\GeoCache\Persistence\GeoCache\GeoCacheService;

/**
 * Class Importer
 *
 * @package Oc\FieldNotes\Import
 */
class Importer
{
    /**
     * @var GeoCacheService
     */
    private $geoCacheService;

    /**
     * @var FieldNoteService
     */
    private $fieldNoteService;

    /**
     * Importer constructor.
     *
     * @param GeoCacheService $geoCacheService
     * @param FieldNoteService $fieldNoteService
     */
    public function __construct(
        GeoCacheService $geoCacheService,
        FieldNoteService $fieldNoteService
    ) {
        $this->geoCacheService = $geoCacheService;
        $this->fieldNoteService = $fieldNoteService;
    }

    /**
     * Import by given context.
     *
     * @param ImportContext $context
     *
     * @return void
     */
    public function import(ImportContext $context)
    {
        $uploadFormData = $context->getFormData();
        $fieldNotes = $context->getFieldNotes();

        /**
         * @var FieldNote $fieldNote
         */
        foreach ($fieldNotes as $fieldNote) {
            $date = new DateTime($fieldNote->noticedAt, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone(date_default_timezone_get()));

            if ($uploadFormData->ignore && $date <= $uploadFormData->ignoreBeforeDate) {
                continue;
            }

            $geoCache = $this->geoCacheService->fetchByWaypoint(
                $fieldNote->waypoint
            );

            $entity = new FieldNoteEntity();
            $entity->userId = $uploadFormData->userId;
            $entity->geocacheId = $geoCache->cacheId;
            $entity->type = LogType::guess($fieldNote->logType);
            $entity->date = $date;
            $entity->text = $fieldNote->notice;

            $this->fieldNoteService->create($entity);
        }
    }
}
