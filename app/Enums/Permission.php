<?php

namespace App\Enums;

enum Permission: string
{
    case MANAGE_USERS = 'manage_users';
    case VIEW_WEBSITE = 'view_website';
}
