<?php

namespace AutomataKit\LaravelAutomationConnect\Filament\Resources;

use AutomataKit\LaravelAutomationConnect\Models\WebhookLog;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebhookLogResource extends Resource
{
    protected static ?string $model = WebhookLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Webhook Logs';

    protected static ?string $pluralModelLabel = 'Webhook Logs';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge(true)
                    ->colors([
                        'warning' => 'processing',
                        'success' => 'success',
                        'danger' => 'failed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('processing_time_ms')
                    ->label('Processing Time (ms)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('processed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service')
                    ->options([
                        'slack' => 'Slack',
                        'n8n' => 'n8n',
                        'zapier' => 'Zapier',
                        'make' => 'Make',
                        'telegram' => 'Telegram',
                        'whatsapp' => 'WhatsApp',
                        'discord' => 'Discord',
                        'hubspot' => 'HubSpot',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'processing' => 'Processing',
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(
                        fn (Builder $builder, array $data): Builder => $builder
                            ->when(
                                $data['created_from'],
                                fn (Builder $builder, $date): Builder => $builder->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $builder, $date): Builder => $builder->whereDate('created_at', '<=', $date),
                            )),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (WebhookLog $record): bool => $record->status === 'failed')
                    ->action(function (WebhookLog $record): void {
                        $record->update(['status' => 'processing']);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWebhookLogs::route('/'),
            'view' => Pages\ViewWebhookLog::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }
}
