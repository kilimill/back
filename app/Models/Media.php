<?php

namespace App\Models;
use Carbon\Carbon;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * @property-read int id
 * @property string model_type
 * @property int model_id
 * @property string|null uuid
 * @property string collection_name
 * @property string name
 * @property string file_name
 * @property string|null mime_type
 * @property string disk
 * @property string|null conversions_disk
 * @property int size
 * @property string manipulations
 * @property string custom_properties
 * @property string generated_conversions
 * @property string responsive_images
 * @property int|null order_column
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static MediaFactory factory($count = null, $state = [])
*/
class Media extends BaseMedia
{
    use HasFactory;
}
