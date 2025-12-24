<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }


    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament/admin/coupon_resource.coupon_updated_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }
}
