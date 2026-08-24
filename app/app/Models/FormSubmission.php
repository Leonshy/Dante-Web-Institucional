<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'name', 'email', 'phone', 'site', 'message', 'status', 'ip_address'])]
class FormSubmission extends Model
{
    use HasAuditing;
}
