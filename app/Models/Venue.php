<?php

namespace App\Models;

use App\Enums\Region;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Venue extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }

    public function conferences(): HasMany
    {
        return $this->hasMany(Conference::class);
    }

    public static function getForm(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('city')
                ->required()
                ->maxLength(255),
            TextInput::make('country')
                ->required()
                ->maxLength(255),
            TextInput::make('postal_code')
                ->required()
                ->maxLength(255),

            //select component region
            Select::make('region')
                ->enum(Region::class)
                ->options(Region::class)
                ->required()
                ->placeholder('Select a region')
                ->searchable()
                ->reactive(),

            SpatieMediaLibraryFileUpload::make('images')
                ->collection('venue-images')
                ->imageEditor()
                ->multiple()
                ->image(),

        ];

    }
}
