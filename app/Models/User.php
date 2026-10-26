<?php

namespace App\Models;

/**
 * Class User
 * Extends Pengguna to maintain 100% backward compatibility with Laravel's built-in User conventions.
 */
class User extends Pengguna
{
    // Inherits all attributes, authenticatable methods, and relations from Pengguna
}
