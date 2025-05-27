<?php

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteUnCommitedImages extends Command
{
    protected $signature = 'app:delete-uncommited-images';
    protected $description = 'delete an uncommited image';

    public function handle()
    {
        $oldImages = Image::where('is_commited', false)
            ->cursor();
        foreach ($oldImages as $image) {
            if ($image->created_at->lt(now()->subMinutes(5))) {
                $image->delete();
            }
        }
    }
}
