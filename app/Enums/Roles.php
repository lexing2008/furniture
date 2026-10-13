<?php

namespace App\Enums;

enum Roles: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case SEO = 'seo';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::MANAGER => 'Manager',
            self::SEO => 'SEO specialist',
            self::CUSTOMER => 'Customer',
        };
    }

    public static function employeeRoles(): array
    {
        return [
            self::ADMIN->value,
            self::MANAGER->value,
            self::SEO->value,
        ];
    }
}
