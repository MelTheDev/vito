<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FailedToDestroyGitHook extends Exception
{
    public function render(Request $request): ?RedirectResponse
    {
        if ($request->header('X-Inertia')) {
            return back()->with('error', 'Failed to destroy git hook.');
        }

        return null;
    }
}
