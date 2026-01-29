# Storage Drivers Module | FluentCart Developer Docs

URL: https://dev.fluentcart.com/modules/storage.html

---


# Storage Drivers Module ​

The Storage Drivers module provides a flexible file storage abstraction layer in FluentCart. It allows you to store and manage digital products, files, and media across different storage providers including local storage and cloud services like Amazon S3.


## Architecture Overview ​


### Core Components ​


#### 1. Storage Drivers ​

Abstract implementations for different storage providers (Local, S3, etc.).


#### 2. File Manager ​

Central service for managing file operations across different storage drivers.


#### 3. Base Driver ​

Abstract base class that all storage drivers must extend.


#### 4. Storage Settings ​

Configuration management for each storage driver.


## Built-in Storage Drivers ​


### Local Storage Driver ​

Stores files locally on the server's file system.

php 
```
use FluentCart\App\Modules\StorageDrivers\Local\Local;

class Local extends BaseStorageDriver
{
    public function __construct()
    {
        parent::__construct(
            __('Local', 'fluent-cart'),
            'local',
            '#136196'
        );
    }

    public function getDescription(): string
    {
        return esc_html__('Local allows to upload file in local file storage', 'fluent-cart');
    }

    public function getDriverClass(): string
    {
        return LocalDriver::class;
    }
}
```


### Amazon S3 Driver ​

Stores files in Amazon S3 buckets for scalable cloud storage.

php 
```
use FluentCart\App\Modules\StorageDrivers\S3\S3;

class S3 extends BaseStorageDriver
{
    public function __construct()
    {
        parent::__construct(
            __('S3', 'fluent-cart'),
            's3',
            '#4f94d4'
        );
    }

    public function getDescription()
    {
        return esc_html__('S3 bucket allows to configure storage options and others for efficient and secure cloud-based file storage', 'fluent-cart');
    }

    public function getDriverClass(): string
    {
        return S3Driver::class;
    }
}
```


## Base Storage Driver ​


### BaseStorageDriver Class ​

All storage drivers extend this abstract class:

php 
```
abstract class BaseStorageDriver implements BaseStorageInterface
{
    public $slug;
    public $title;
    public $brandColor = '#ccc';
    protected $driverHandler;

    public function __construct($title, $slug, $brandColor)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->brandColor = $brandColor;
        $this->driverHandler = 'fluent_cart_storage_settings_' . $slug;
    }

    abstract public function getLogo();
    abstract public function getDescription();
    abstract public function getSettings();
    abstract public function fields();
    abstract public function hiddenSettingKeys(): array;
}
```


### BaseStorageInterface ​

php 
```
interface BaseStorageInterface
{
    public function isEnabled(): bool;
}
```


## File Manager ​


### FileManager Class ​

The central service for managing file operations:

php 
```
use FluentCart\App\Services\FileSystem\FileManager;

class FileManager
{
    private $driver;
    protected ?string $dirPath;
    protected ?string $dirName;

    public function __construct(
        string $driver = 'local',
        ?string $dirPath = null,
        ?string $dirName = null,
        $inActiveMode = false
    ) {
        $this->dirName = $dirName;
        $this->dirPath = $dirPath;
        $this->driver = $this->resolveDriver($driver, $inActiveMode);
    }

    public function uploadFile(string $localFilePath, string $uploadToFilePath, $file, array $params = [])
    {
        return $this->driver->uploadFile($localFilePath, $uploadToFilePath, $file, $params);
    }

    public function downloadFile(string $filePath, string $fileName, $bucket = null)
    {
        return $this->driver->downloadFile($filePath, $fileName, $bucket);
    }

    public function getSignedDownloadUrl(string $filePath, $bucket = null, $productDownload = null): ?string
    {
        return $this->driver->getSignedDownloadUrl($filePath, $bucket, $productDownload);
    }

    public function deleteFile(string $filePath, $bucket = null)
    {
        return $this->driver->deleteFile($filePath, $bucket);
    }

    public function listFiles(array $params = [])
    {
        return $this->driver->listFiles($params);
    }
}
```


## Base Driver Implementation ​


### BaseDriver Class ​

All storage driver implementations extend this class:

php 
```
abstract class BaseDriver
{
    protected ?string $dirPath;
    protected ?string $dirName;
    protected ?BaseStorageDriver $storageDriver;

    public function __construct(?string $dirPath = null, ?string $dirName = null)
    {
        $this->dirName = $dirName;
        $this->dirPath = $dirPath;
    }

    public abstract function listFiles(array $params = []);
    public abstract function uploadFile($localFilePath, $uploadToFilePath, $file, $params = []);
    public abstract function downloadFile(string $filePath);
    public abstract function getSignedDownloadUrl(string $filePath, $bucket = null, $productDownload = null);
    public abstract function getFilePath(string $filePath);
    public abstract function deleteFile(string $filePath, $bucket = null);
}
```


## Local Storage Implementation ​


### LocalDriver Class ​

php 
```
class LocalDriver extends BaseDriver
{
    public function __construct(string $dirPath = null, string $dirName = null)
    {
        parent::__construct($dirPath, $dirName);
        $this->dirName = $dirName ?? 'fluent-cart';
        $this->dirPath = $dirPath ?? $this->getDefaultDirPath();
        $this->storageDriver = new LocalStorageDriver();
        $this->ensureDirectoryExist();
    }

    protected function getDefaultDirPath(): string
    {
        return wp_get_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . $this->getDirName();
    }

    private function ensureDirectoryExist()
    {
        $uploadDirectory = $this->getDefaultDirPath();
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory);
        }
    }

    public function uploadFile($localFilePath, $uploadToFilePath, $file, $params = [])
    {
        $destinationPath = $this->dirPath . DIRECTORY_SEPARATOR . $uploadToFilePath;
        
        // Ensure directory exists
        $destinationDir = dirname($destinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        if (move_uploaded_file($localFilePath, $destinationPath)) {
            return [
                'success' => true,
                'file_path' => $uploadToFilePath,
                'file_url' => $this->getFileUrl($uploadToFilePath)
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to move uploaded file'
        ];
    }

    public function downloadFile(string $filePath)
    {
        $fullPath = $this->dirPath . DIRECTORY_SEPARATOR . $filePath;
        
        if (!file_exists($fullPath)) {
            return [
                'success' => false,
                'error' => 'File not found'
            ];
        }

        $fileName = basename($filePath);
        $fileSize = filesize($fullPath);

        $this->setDownloadHeader($fileName, $fullPath, $fileSize);
        
        readfile($fullPath);
        exit;
    }

    public function getSignedDownloadUrl(string $filePath, $bucket = null, $productDownload = null): ?string
    {
        // For local storage, return direct file URL
        return $this->getFileUrl($filePath);
    }

    public function getFilePath(string $filePath)
    {
        return $this->dirPath . DIRECTORY_SEPARATOR . $filePath;
    }

    public function deleteFile(string $filePath, $bucket = null)
    {
        $fullPath = $this->dirPath . DIRECTORY_SEPARATOR . $filePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    private function getFileUrl(string $filePath): string
    {
        $uploadDir = wp_get_upload_dir();
        return $uploadDir['baseurl'] . '/' . $this->getDirName() . '/' . $filePath;
    }
}
```


## S3 Storage Implementation ​


### S3Driver Class ​

php 
```
class S3Driver extends BaseDriver
{
    private string $accessKey;
    private string $secretKey;
    private string $bucket;
    private string $region;

    public function __construct(string $dirPath = null, string $dirName = null)
    {
        parent::__construct($dirPath, $dirName);
        
        $getSettings = (new S3Settings())->get();
        
        $this->secretKey = Arr::get($getSettings, 'secret_key', '');
        $this->accessKey = Arr::get($getSettings, 'access_key', '');
        $this->bucket = Arr::get($getSettings, 'bucket', '');
        $this->region = Arr::get($getSettings, 'region', '');
        $this->storageDriver = new S3StorageDriver();
    }

    public function uploadFile($localFilePath, $uploadToFilePath, $file, $params = [])
    {
        $fileSize = $file->toArray()['size_in_bytes'];
        
        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => [
                    'key' => $this->accessKey,
                    'secret' => $this->secretKey,
                ],
            ]);

            $result = $s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $uploadToFilePath,
                'SourceFile' => $localFilePath,
                'ContentType' => $file->getMimeType(),
                'ACL' => 'private', // Private by default for security
            ]);

            return [
                'success' => true,
                'file_path' => $uploadToFilePath,
                'file_url' => $result['ObjectURL']
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getSignedDownloadUrl(string $filePath, $bucket = null, $productDownload = null): ?string
    {
        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => [
                    'key' => $this->accessKey,
                    'secret' => $this->secretKey,
                ],
            ]);

            $bucket = $bucket ?? $this->bucket;
            $expiration = '+1 hour'; // Default expiration

            // Custom expiration for product downloads
            if ($productDownload) {
                $expiration = '+24 hours'; // Longer expiration for product downloads
            }

            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $filePath,
            ]);

            $request = $s3Client->createPresignedRequest($cmd, $expiration);
            
            return (string) $request->getUri();

        } catch (Exception $e) {
            return null;
        }
    }

    public function deleteFile(string $filePath, $bucket = null)
    {
        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => [
                    'key' => $this->accessKey,
                    'secret' => $this->secretKey,
                ],
            ]);

            $bucket = $bucket ?? $this->bucket;

            $result = $s3Client->deleteObject([
                'Bucket' => $bucket,
                'Key' => $filePath,
            ]);

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    public function buckets()
    {
        return S3BucketList::get(
            $this->secretKey,
            $this->accessKey,
            $this->region
        );
    }
}
```


## Storage Settings ​


### BaseStorageSettings Class ​

php 
```
abstract class BaseStorageSettings
{
    protected $settings;
    protected $driverHandler = 'fluent_cart_storage_settings_';

    public function __construct($slug)
    {
        $settings = fluent_cart_get_option($this->driverHandler.$slug, []);
        $this->settings = wp_parse_args($settings, $this->getDefaultSettings());
    }

    abstract protected function getDefaultSettings();
    abstract public function isActive();

    public function get()
    {
        return $this->settings;
    }
}
```


### Local Storage Settings ​

php 
```
class LocalSettings extends BaseStorageSettings
{
    protected function getDefaultSettings()
    {
        return [
            'is_active' => 'no'
        ];
    }

    public function isActive()
    {
        return $this->settings['is_active'] === 'yes';
    }
}
```


### S3 Storage Settings ​

php 
```
class S3Settings extends BaseStorageSettings
{
    protected function getDefaultSettings()
    {
        return [
            'is_active' => 'no',
            'access_key' => '',
            'secret_key' => '',
            'bucket' => '',
            'region' => 'us-east-1',
            'buckets' => []
        ];
    }

    public function isActive()
    {
        $isActive = $this->settings['is_active'] === 'yes';
        $hasSelectedBuckets = !empty($this->settings['buckets']) && 
                             is_array($this->settings['buckets']) && 
                             count($this->settings['buckets']) > 0;
        
        return $isActive && $hasSelectedBuckets;
    }
}
```


## Creating Custom Storage Drivers ​


### 1. Create Storage Driver Class ​

php 
```
<?php
namespace FluentCart\App\Modules\StorageDrivers\CustomDriver;

use FluentCart\App\Modules\StorageDrivers\BaseStorageDriver;
use FluentCart\App\Vite;

class CustomDriver extends BaseStorageDriver
{
    public function __construct()
    {
        parent::__construct(
            __('Custom Storage', 'fluent-cart'),
            'custom_driver',
            '#your-brand-color'
        );
    }

    public function getLogo(): string
    {
        return Vite::getAssetUrl("images/storage-drivers/custom-logo.svg");
    }

    public function getDescription(): string
    {
        return esc_html__('Custom storage driver for your specific needs', 'fluent-cart');
    }

    public function isEnabled(): bool
    {
        return (new CustomDriverSettings())->isActive();
    }

    public function getSettings()
    {
        return (new CustomDriverSettings())->get();
    }

    public function fields(): array
    {
        return [
            'view' => [
                'title' => __('Custom Driver Settings', 'fluent-cart'),
                'type' => 'section',
                'disable_nesting' => true,
                'columns' => [
                    'default' => 1,
                    'md' => 1,
                    'lg' => 1
                ],
                'schema' => [
                    'is_active' => [
                        'value' => '',
                        'label' => __('Enable custom driver', 'fluent-cart'),
                        'type' => 'checkbox'
                    ],
                    'api_key' => [
                        'value' => '',
                        'label' => __('API Key', 'fluent-cart'),
                        'type' => 'text',
                        'required' => true
                    ],
                    'endpoint' => [
                        'value' => '',
                        'label' => __('Endpoint URL', 'fluent-cart'),
                        'type' => 'text',
                        'required' => true
                    ]
                ]
            ]
        ];
    }

    public function getDriverClass(): string
    {
        return CustomDriverImplementation::class;
    }

    public function hiddenSettingKeys(): array
    {
        return ['api_key', 'secret_key'];
    }

    public function verifyConnectInfo(array $data, $args = [])
    {
        // Implement connection verification logic
        $apiKey = $data['api_key'] ?? '';
        $endpoint = $data['endpoint'] ?? '';

        if (empty($apiKey) || empty($endpoint)) {
            return new \WP_Error('missing_credentials', 'API Key and Endpoint are required');
        }

        // Test connection
        $response = wp_remote_get($endpoint . '/test', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey
            ],
            'timeout' => 10
        ]);

        if (is_wp_error($response)) {
            return new \WP_Error('connection_failed', 'Failed to connect to custom storage service');
        }

        return true;
    }
}
```


### 2. Create Driver Implementation ​

php 
```
<?php
namespace FluentCart\App\Services\FileSystem\Drivers\CustomDriver;

use FluentCart\App\Services\FileSystem\Drivers\BaseDriver;
use FluentCart\App\Modules\StorageDrivers\CustomDriver\CustomDriver as CustomStorageDriver;

class CustomDriverImplementation extends BaseDriver
{
    private string $apiKey;
    private string $endpoint;

    public function __construct(string $dirPath = null, string $dirName = null)
    {
        parent::__construct($dirPath, $dirName);
        
        $settings = (new CustomDriverSettings())->get();
        $this->apiKey = $settings['api_key'] ?? '';
        $this->endpoint = $settings['endpoint'] ?? '';
        $this->storageDriver = new CustomStorageDriver();
    }

    public function uploadFile($localFilePath, $uploadToFilePath, $file, $params = [])
    {
        try {
            $response = wp_remote_post($this->endpoint . '/upload', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'multipart/form-data'
                ],
                'body' => [
                    'file' => new \CURLFile($localFilePath),
                    'path' => $uploadToFilePath,
                    'metadata' => json_encode([
                        'size' => filesize($localFilePath),
                        'type' => mime_content_type($localFilePath)
                    ])
                ],
                'timeout' => 60
            ]);

            if (is_wp_error($response)) {
                return [
                    'success' => false,
                    'error' => $response->get_error_message()
                ];
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($data['success']) {
                return [
                    'success' => true,
                    'file_path' => $uploadToFilePath,
                    'file_url' => $data['file_url']
                ];
            }

            return [
                'success' => false,
                'error' => $data['error'] ?? 'Upload failed'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getSignedDownloadUrl(string $filePath, $bucket = null, $productDownload = null): ?string
    {
        try {
            $response = wp_remote_post($this->endpoint . '/generate-url', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'file_path' => $filePath,
                    'expires_in' => $productDownload ? 86400 : 3600 // 24h for products, 1h for others
                ]),
                'timeout' => 30
            ]);

            if (is_wp_error($response)) {
                return null;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            return $data['download_url'] ?? null;

        } catch (Exception $e) {
            return null;
        }
    }

    public function deleteFile(string $filePath, $bucket = null)
    {
        try {
            $response = wp_remote_request($this->endpoint . '/delete', [
                'method' => 'DELETE',
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'file_path' => $filePath
                ]),
                'timeout' => 30
            ]);

            if (is_wp_error($response)) {
                return false;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            return $data['success'] ?? false;

        } catch (Exception $e) {
            return false;
        }
    }

    public function listFiles(array $params = [])
    {
        try {
            $response = wp_remote_get($this->endpoint . '/files', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey
                ],
                'body' => $params,
                'timeout' => 30
            ]);

            if (is_wp_error($response)) {
                return [];
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            return $data['files'] ?? [];

        } catch (Exception $e) {
            return [];
        }
    }

    public function downloadFile(string $filePath)
    {
        $downloadUrl = $this->getSignedDownloadUrl($filePath);
        
        if (!$downloadUrl) {
            return [
                'success' => false,
                'error' => 'Failed to generate download URL'
            ];
        }

        // Redirect to download URL
        wp_redirect($downloadUrl);
        exit;
    }

    public function getFilePath(string $filePath)
    {
        return $this->endpoint . '/files/' . $filePath;
    }
}
```


### 3. Create Settings Class ​

php 
```
<?php
namespace FluentCart\App\Modules\StorageDrivers\CustomDriver;

use FluentCart\App\Modules\StorageDrivers\BaseStorageSettings;

class CustomDriverSettings extends BaseStorageSettings
{
    protected function getDefaultSettings()
    {
        return [
            'is_active' => 'no',
            'api_key' => '',
            'endpoint' => ''
        ];
    }

    public function isActive()
    {
        return $this->settings['is_active'] === 'yes';
    }
}
```


### 4. Register Custom Driver ​

php 
```
// In your plugin's initialization
add_action('fluentcart_loaded', function($app) {
    // Register custom storage driver
    $customDriver = new \FluentCart\App\Modules\StorageDrivers\CustomDriver\CustomDriver();
    $customDriver->init();
});
```


## File Operations ​


### Uploading Files ​

php 
```
use FluentCart\App\Services\FileSystem\FileManager;

// Initialize file manager with specific driver
$fileManager = new FileManager('s3', 'products', 'digital-files');

// Upload a file
$result = $fileManager->uploadFile(
    $localFilePath,           // Local file path
    'product-123/file.pdf',   // Remote file path
    $uploadedFile,            // File object
    ['metadata' => 'value']   // Additional parameters
);

if ($result['success']) {
    echo "File uploaded successfully: " . $result['file_url'];
} else {
    echo "Upload failed: " . $result['error'];
}
```


### Downloading Files ​

php 
```
// Generate signed download URL
$downloadUrl = $fileManager->getSignedDownloadUrl(
    'product-123/file.pdf',
    'my-bucket',
    $productDownload // Optional: product download object
);

if ($downloadUrl) {
    // Redirect user to download URL
    wp_redirect($downloadUrl);
    exit;
}
```


### Listing Files ​

php 
```
// List files with search
$files = $fileManager->listFiles([
    'search' => 'product-123',
    'limit' => 50,
    'offset' => 0
]);

foreach ($files as $file) {
    echo "File: " . $file['name'] . " (" . $file['size'] . " bytes)\n";
}
```


### Deleting Files ​

php 
```
// Delete a file
$success = $fileManager->deleteFile('product-123/file.pdf', 'my-bucket');

if ($success) {
    echo "File deleted successfully";
} else {
    echo "Failed to delete file";
}
```


## Hooks and Filters ​


### Storage Driver Hooks ​

php 
```
// Modify file upload parameters
add_filter('fluent_cart/storage/upload_params', function($params, $file, $driver) {
    // Add custom metadata
    $params['custom_metadata'] = [
        'uploaded_by' => get_current_user_id(),
        'upload_time' => current_time('mysql')
    ];
    
    return $params;
}, 10, 3);

// Modify download URL generation
add_filter('fluent_cart/storage/download_url', function($url, $filePath, $driver) {
    // Add custom tracking parameters
    $url .= '&tracking_id=' . wp_generate_uuid4();
    
    return $url;
}, 10, 3);

// Custom file validation
add_filter('fluent_cart/storage/validate_file', function($isValid, $file, $driver) {
    // Custom validation logic
    if ($file->getSize() > 100 * 1024 * 1024) { // 100MB limit
        return new \WP_Error('file_too_large', 'File size exceeds 100MB limit');
    }
    
    return $isValid;
}, 10, 3);
```


### Storage Driver Actions ​

php 
```
// After file upload
add_action('fluent_cart/storage/file_uploaded', function($filePath, $driver, $result) {
    // Log upload activity
    error_log("File uploaded: {$filePath} using {$driver}");
    
    // Send notification
    wp_mail(
        get_option('admin_email'),
        'File Uploaded',
        "A new file has been uploaded: {$filePath}"
    );
}, 10, 3);

// Before file deletion
add_action('fluent_cart/storage/before_file_delete', function($filePath, $driver) {
    // Create backup before deletion
    $backupPath = 'backups/' . basename($filePath);
    // Backup logic here
}, 10, 2);

// After file deletion
add_action('fluent_cart/storage/file_deleted', function($filePath, $driver) {
    // Log deletion
    error_log("File deleted: {$filePath} from {$driver}");
}, 10, 2);
```

Next Steps:

- Modules Overview - Back to modules overview

