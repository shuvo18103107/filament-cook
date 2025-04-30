<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TalkResource\Pages;
use App\Models\Talk;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class TalkResource extends Resource
{
    protected static ?string $model = Talk::class;

//    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationGroup = 'Laracon Dhaka';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Talk::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->filtersTriggerAction(function ($action) {
                return $action->button()->label('Filters');
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->description(function (Talk $record) {
                        return Str::limit($record->abstract, 40);
                    }),
                Tables\Columns\ImageColumn::make('speaker?.avatar')
                    ->label('Speaker Avatar')
                    ->circular()
                    ->defaultImageUrl(function (Talk $record) {
                        return $record->speaker?->avatar
                            ? Storage::url($record->speaker->avatar)
                            : 'https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=' . urlencode($record->speaker?->name ?? 'Unknown');
                    }),

                Tables\Columns\TextColumn::make('speaker.name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\IconColumn::make('new_talk')->boolean()
                    ->label('New Talk')
                    ->sortable(),

                // Tables\Columns\ToggleColumn::make('new_talk')
                //     ->label('New Talk')
                //     ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(function (TalkStatus $state) {
                        return $state->getColor();
                    }),

                Tables\Columns\IconColumn::make('length')
                    ->icon(function (TalkLength $state) {
                        return match ($state) {
                            TalkLength::NORMAL => 'heroicon-o-megaphone',
                            TalkLength::LIGHTNING => 'heroicon-o-bolt',
                            TalkLength::KEYNOTE => 'heroicon-o-key',
                        };
                    })
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('new_talk'),
                Tables\Filters\SelectFilter::make('speaker')
                    ->relationship('speaker', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('has_avatar')
                    ->label('Show Only Speakers With Avatars')
                    ->toggle()
                    ->query(function ($query) {
                        return $query->whereHas('speaker', function (Builder $query) {
                            $query->whereNotNull('avatar');
                        });
                    })
            ])
            ->actions([
                ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->slideOver(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->visible(function ($record) {
                            return $record->status !== (TalkStatus::APPROVED);
                        })
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Talk $record) {
                            $record->approve();
                        })->after(function () {
                            Notification::make()->success()->title('This talk was approved')
                                ->duration(1000)
                                ->body('The speaker has been notified and the talk has been added to the conference schedule.')
                                ->send();
                        }),
                    Tables\Actions\Action::make('reject')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(function ($record) {
                            return $record->status !== (TalkStatus::REJECTED);
                        })
                        ->action(function (Talk $record) {
                            $record->reject();
                        })->after(function () {
                            Notification::make()->danger()->title('This talk was rejected')
                                ->duration(1000)
                                ->body('The speaker has been notified.')
                                ->send();
                        }),
                    Tables\Actions\Action::make('submit')
                        ->icon('heroicon-o-check')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->visible(function ($record) {
                            return $record->status !== (TalkStatus::SUBMITTED);
                        })
                        ->action(function (Talk $record) {
                            $record->submit();
                        })->after(function () {
                            Notification::make()->success()->title('This talk was submitted')
                                ->duration(1000)
                                ->body('The speaker has been notified.')
                                ->send();
                        })
                ]),


            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each->approve();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->tooltip('This will export all records visible in the table. Adjust filters to export a subset of records.')
                    ->action(function ($livewire) {

                        dump($livewire->getFilteredTableQuery()->count());
                    })
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTalks::route('/'),
            'create' => Pages\CreateTalk::route('/create'),

            // 'edit' => Pages\EditTalk::route('/{record}/edit'),
        ];
    }
}
