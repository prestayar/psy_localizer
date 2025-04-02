<?php
/**
 * Prestashop Module Development Kit
 *
 * @author     Hashem Afkhami <hashemafkhami89@gmail.com>
 * @copyright  (c) 2025 - PrestaWare Team
 * @website    https://prestaware.com
 * @license    https://www.gnu.org/licenses/gpl-3.0.html [GNU General Public License]
 */
namespace PrestaSDK\Model;

abstract class BaseModel extends \ObjectModel
{
    const CREATED_AT_COLUMN = null;
    const UPDATED_AT_COLUMN = null;
    const ID_SHOP_COLUMN = null;
    const ID_LANG_COLUMN = null;
    const STATUS_COLUMN = null;

    protected static $user_id_lang;

    private $requireAutoSet = true;
    private $requirePreSave = true;

    public function getCreatedAtColumn()
    {
        return static::CREATED_AT_COLUMN;
    }

    public function getUpdatedAtColumn()
    {
        return static::UPDATED_AT_COLUMN;
    }

    public function getIdShopColumn()
    {
        return static::ID_SHOP_COLUMN;
    }

    public function getStatusColumn()
    {
        return static::STATUS_COLUMN;
    }

    public function autoSetterOnSave($autoDate = true)
    {
        if ((int) $this->id > 0) {
            // Updating Data
            if (!empty($this->getUpdatedAtColumn())) {
                $this->{$this->getUpdatedAtColumn()} = date('Y-m-d H:i:s');
            }
        } else {
            // Adding Data
            if (!empty($this->getIdShopColumn()) && empty($this->{$this->getIdShopColumn()})) {
                $this->{$this->getIdShopColumn()} = (int) $this->getCurrentShopId();
            }

            if (!empty($this->getIdLangColumn()) && empty($this->{$this->getIdLangColumn()})) {
                $this->{$this->getIdLangColumn()} = (int) $this->getCurrentLangId();
            }

            if ($autoDate && !empty($this->getCreatedAtColumn())) {
                $this->{$this->getCreatedAtColumn()} = date('Y-m-d H:i:s');
            }

            if ($autoDate && !empty($this->getUpdatedAtColumn())) {
                $this->{$this->getUpdatedAtColumn()} = date('Y-m-d H:i:s');
            }
        }

        $this->requireAutoSet = false;
    }

    public function preSaveActions()
    {
        $definition = \ObjectModel::getDefinition($this);
        if (is_array($definition)) {
            foreach ($definition['fields'] as $field => $def) {
                if (isset($def['data_type']) && $def['data_type'] === 'json' && isset($this->{$field}) && !empty($this->{$field})) {
                    if (is_array($this->{$field})) {
                        $this->{$field} = json_encode($this->{$field}, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }
                }
            }
        }

        $this->requirePreSave = false;
        return $this;
    }

    public function safeValidation()
    {
        if ($this->requireAutoSet) {
            $this->autoSetterOnSave();
        }

        if ($this->requirePreSave) {
            $this->preSaveActions();
        }

        try {
            return $this->validateFields();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function safeSave($null_values = true, $auto_date = true)
    {
        if ($this->safeValidation()) {
            return $this->save($null_values, $auto_date);
        }
        return false;
    }

    public function add($auto_date = true, $null_values = false)
    {
        if ($this->requirePreSave) {
            $this->preSaveActions();
        }

        if ($this->requireAutoSet) {
            $this->autoSetterOnSave($auto_date);
        }
        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        if ($this->requirePreSave) {
            $this->preSaveActions();
        }

        if ($this->requireAutoSet) {
            $this->autoSetterOnSave();
        }
        return parent::update($null_values);
    }

    public static function getCurrentShopId()
    {
        return \Shop::getContextShopID();
    }

    public function isLoaded()
    {
        return $this->id && !empty($this->id);
    }

    public static function getCountsByStatus($status, $extraQuery = null)
    {
        $idShopColumn = static::ID_SHOP_COLUMN;
        $statusColumn = static::STATUS_COLUMN;

        $query = 'SELECT COUNT(*) 
                    FROM ' . _DB_PREFIX_ . static::TABLE . ' AS a
                    WHERE a.`' . $idShopColumn . '` = ' . (int) \Shop::getContextShopID();

        if (is_int($status)) {
            $query .= ' AND a.`' . $statusColumn . '` = ' . (int) $status;
        }

        if (is_array($status) && !empty($status)) {
            $query .= ' AND (';

            for ($i = 0; $i < count($status); $i++) {
                if ($i != 0) {
                    $query .= ' OR ';
                }
                $query .= ' a.`' . $statusColumn . '` = ' . (int) $status[$i];
            }

            $query .= ' ) ';
        }

        if (!empty($extraQuery)) {
            $query .= $extraQuery;
        }

        return \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public function toggleStatus()
    {
        if (!empty(static::STATUS_COLUMN)) {
            if (!array_key_exists(static::STATUS_COLUMN, $this)) {
                throw new \PrestaShopException('property "' . static::STATUS_COLUMN . '" is missing in object ' . get_class($this));
            }

            $this->setFieldsToUpdate([static::STATUS_COLUMN => true]);
            $this->{static::STATUS_COLUMN} = !(int) $this->{static::STATUS_COLUMN};
            return $this->update(false);
        }
        return parent::toggleStatus();
    }

    public static function getIdLang()
    {
        if (empty(static::$user_id_lang)) {
            if (in_array(
                \Context::getContext()->controller->controller_type,
                ['front', 'modulefront']
            )) {
                static::$user_id_lang = \Context::getContext()->customer->id_lang;
            } else {
                static::$user_id_lang = \Context::getContext()->employee->id_lang;
            }
        }
        return static::$user_id_lang;
    }

    public static function getQueryValue($value)
    {
        return is_numeric($value) ? $value : "'$value'";
    }

    public static function getTableRows($table, array $wheresArray = [], $orderBy = null)
    {
        $query = new \DbQuery();
        $sql = ' 1 = 1 ';

        $query->select('*');
        $query->from($table);

        foreach ($wheresArray as $dataKey => $dataValue) {
            if (is_array($dataValue)) {
                foreach ($dataValue as $value) {
                    $sql .= " OR $dataKey = " . static::getQueryValue($value);
                }
            } else {
                $query->where($dataKey . ' = ' . static::getQueryValue($dataValue));
            }
        }

        $query->where($sql);

        if (is_array($orderBy)) {
            foreach ($orderBy as $order) {
                $query->orderBy($order);
            }
        } else {
            $query->orderBy($orderBy);
        }

        return \Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
    }

    public function validationsByModel($fields, array $values = null)
    {
        $values = !empty($values) ? $values : $_POST;
        $errors = [];

        $definition = \ObjectModel::getDefinition($this);
        if (is_array($definition)) {
            foreach ($definition['fields'] as $field => $def) {
                if (!isset($fields[$field])) {
                    continue;
                }

                $value = isset($values[$field]) ? $values[$field] : null;
                $name = isset($fields[$field]) ? $fields[$field] : $field;

                // Checking for required fields
                if (isset($def['required']) && $def['required'] && empty($value) && $value !== '0') {
                    $errors[] = sprintf('The field "%s" is required!', $name);
                }

                // Checking for maximum fields sizes
                if (isset($def['size']) && !empty($value) && \Tools::strlen($value) > $def['size']) {
                    $errors[] = sprintf('The field "%s" is too long, the maximum length for this field is "%s" characters!', $name, $def['size']);
                }

                // Checking for fields validity
                if ((!empty($value) || $value === '0') && isset($def['validate'])) {
                    $methodName = $def['validate'];
                    if (!\Validate::$methodName($value) && (!empty($value) || $def['required'])) {
                        $errors[] = sprintf('The value entered for the field "%s" is invalid!', $name);
                    }
                }

                $this->{$field} = $value;
            }
        }

        return $errors;
    }

    public static function getIdLangColumn()
    {
        return static::ID_LANG_COLUMN;
    }

    public static function getCurrentLangId()
    {
        return \Validate::isLoadedObject(\Context::getContext()->language) ? \Context::getContext()->language->id : \Configuration::get('PS_LANG_DEFAULT');
    }
}