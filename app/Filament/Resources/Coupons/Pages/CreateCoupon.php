<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Resources\Coupons\CouponResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament/admin/coupon_resource.coupon_created_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }
}
