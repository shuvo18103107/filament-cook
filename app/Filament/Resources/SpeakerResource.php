<?php

namespace App\Filament\Resources;

use App\Enums\TalkStatus;
use App\Filament\Resources\SpeakerResource\Pages;
use App\Filament\Resources\SpeakerResource\RelationManagers\TalksRelationManager;
use App\Models\Speaker;
use Filament\Forms\Form;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class SpeakerResource extends Resource
{
    protected static ?string $model = Speaker::class;

//    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Laracon Dhaka';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Speaker::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular()
                    ->defaultImageUrl(function (Speaker $record) {
                        return $record->avatar
                            ? Storage::url($record->avatar)
                            : 'https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=' . urlencode($record->name);
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('twitter_handle')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Personal Information')
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('avatar')
                            ->circular(),
                        Group::make()
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('email'),
                                TextEntry::make('twitter_handle')
                                    ->label('Twitter')
                                    ->getStateUsing(function ($record) {
                                        return '@' . $record->twitter_handle;
                                    })
                                    ->url(function ($record) {
                                        return 'https://twitter.com/' . $record->twitter_handle;
                                    }),
                                TextEntry::make('has_spoken')
                                    ->getStateUsing(function ($record) {
                                        return $record->talks()->where('status', TalkStatus::APPROVED)->count()
                                            > 0 ? 'Previous Speaker' : 'Has Not Spoken';
                                    })
                                    ->badge()
                                    ->color(function ($state) {
                                        if ($state === 'Previous Speaker') {
                                            return 'success';
                                        }
                                        return 'primary';
                                    }),
                            ]),
                    ]),
                Section::make('Other Information')
                    ->schema([
                        TextEntry::make('bio')
                            ->extraAttributes(['class' => 'prose dark:prose-invert'])
                            ->html(),
                        TextEntry::make('qualifications'),
                    ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TalksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpeakers::route('/'),
            'create' => Pages\CreateSpeaker::route('/create'),
            'view' => Pages\ViewSpeaker::route('/{record}'),
        ];
    }
}
