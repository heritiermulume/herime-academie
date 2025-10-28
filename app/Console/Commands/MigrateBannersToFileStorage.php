<?php

namespace App\Console\Commands;

use App\Models\Banner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateBannersToFileStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banners:migrate-to-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate banner images from base64 to file storage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of banner images...');
        
        $banners = Banner::all();
        $migrated = 0;
        
        foreach ($banners as $banner) {
            $this->info("Processing banner #{$banner->id}: {$banner->title}");
            
            // Migrate main image
            if ($banner->image && str_starts_with($banner->image, 'data:')) {
                try {
                    $path = $this->saveBase64Image($banner->image, 'image');
                    if ($path) {
                        $banner->image = asset('storage/' . $path);
                        $this->info("  ✓ Main image migrated");
                    }
                } catch (\Exception $e) {
                    $this->error("  ✗ Error migrating main image: " . $e->getMessage());
                }
            }
            
            // Migrate mobile image
            if ($banner->mobile_image && str_starts_with($banner->mobile_image, 'data:')) {
                try {
                    $path = $this->saveBase64Image($banner->mobile_image, 'mobile');
                    if ($path) {
                        $banner->mobile_image = asset('storage/' . $path);
                        $this->info("  ✓ Mobile image migrated");
                    }
                } catch (\Exception $e) {
                    $this->error("  ✗ Error migrating mobile image: " . $e->getMessage());
                }
            }
            
            $banner->save();
            $migrated++;
        }
        
        $this->info("Migration completed! {$migrated} banner(s) migrated.");
        
        return 0;
    }
    
    /**
     * Save base64 image to file storage
     */
    private function saveBase64Image($base64String, $type)
    {
        // Extract mime type and data
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
            $extension = $matches[1];
            $data = substr($base64String, strpos($base64String, ',') + 1);
            $data = base64_decode($data);
            
            if ($data === false) {
                throw new \Exception('Failed to decode base64 image');
            }
            
            // Generate filename
            $filename = 'banner_' . time() . '_' . $type . '_' . uniqid() . '.' . $extension;
            $path = 'banners/' . $filename;
            
            // Save to storage
            Storage::disk('public')->put($path, $data);
            
            return $path;
        }
        
        return null;
    }
}

