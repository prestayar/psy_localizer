## Chapter 5: Data Management (Models)
This chapter shows you how to easily work with the database using the BaseModel class in PrestaSDK. This class extends PrestaShop's ObjectModel and automates many repetitive tasks.
### 5.1. Creating a Model
To define a new entity that maps to a database table, create a class in the src/Entity/ directory (or any other path you prefer) and extend it from PrestaSDK\V071\Model\BaseModel.
Main Steps:
- Extend: Your class must extend BaseModel.
- Define Constants: Define the TABLE and ID constants to specify the table name and its primary key.
- Define $definition: Define the model's structure, including the table name, primary key, and fields, in the static $definition property. This structure is identical to the standard PrestaShop ObjectModel.
- (Optional) Define Special Columns: To enable BaseModel's automatic features, define the names of the date, status, and shop columns in the respective constants.
Complete Example (from the wabulkupdate module):

```php
// src/Entity/File.php
namespace PrestaWare\WaBulkUpdate\Entity;
use PrestaSDK\V071\Model\BaseModel;

class File extends BaseModel
{
    // 1. Define main constants
    const TABLE = 'wabulkupdate_file';
    const ID = 'id_wabulkupdate_file';

    // 2. Define special constants to enable BaseModel features
    const CREATED_AT_COLUMN = 'date_add';
    const UPDATED_AT_COLUMN = 'date_upd';
    const STATUS_COLUMN = 'status';
    // const ID_SHOP_COLUMN = 'id_shop'; // If your table is shop-specific

    // 3. Define class properties
    public $id;
    public $file_name;
    public $status;
    public $date_add;
    public $date_upd;

    // 4. Define the model structure for PrestaShop
    public static $definition = [
        'table' => self::TABLE,
        'primary' => self::ID,
        'fields' => [
            'file_name' => ['type' => self::TYPE_STRING, 'validate' => 'isFileName', 'required' => true, 'size' => 255],
            'status' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
}
```
### 5.2. BaseModel Features
The BaseModel class adds many automatic functionalities to your model:
#### Automatic Date Management
If you define the CREATED_AT_COLUMN and UPDATED_AT_COLUMN constants:
- When creating a new record, both the date_add and date_upd fields are automatically populated with the current timestamp.
- When updating a record, the date_upd field is automatically updated.
#### Automatic Status Management
If you define the STATUS_COLUMN constant, you can use the toggleStatus() method to enable/disable a record. This method automatically flips the value of the status column and saves the record, which is very useful in a HelperList.
```php
$file = new File($id);
$file->toggleStatus(); // Status changes from 0 to 1 or vice-versa
```
#### Safe Saving with Validation (safeSave)
Instead of calling save() directly, you can use safeSave(). This method automatically runs all validations defined in $definition before saving. If the data is invalid, it returns false and prevents an invalid record from being saved.
```php
$file = new File();
$file->file_name = 'invalid-name-@!#.xlsx'; // Invalid
$file->status = 1;

if ($file->safeSave()) {
    // This code will not be executed
    echo "File saved successfully!";
} else {
    echo "Validation failed!";
}
```
#### Automatic id_shop Management
If your module operates in a multishop context and your table has an id_shop column, just define the ID_SHOP_COLUMN constant. BaseModel will automatically store the current shop's ID when creating a new record.
