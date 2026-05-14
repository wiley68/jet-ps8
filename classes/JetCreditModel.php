<?php
class JetCreditModel extends ObjectModel
{

    public static $definition = array(
        'table' => 'jet_kop',
        'primary' => 'id',
        'fields' => array(
            'jet_product_id' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 20),
            'jet_product_percent' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPercentage', 'required' => true),
            'jet_product_meseci' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 50),
            'jet_product_price' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
            'jet_product_start' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
            'jet_product_end' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
        ),
    );

    public static function insertIfNotExists(
        $jet_product_id,
        $jet_product_percent,
        $jet_product_meseci,
        $jet_product_price,
        $jet_product_start,
        $jet_product_end
    ) {
        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from('jet_kop');
        $sql->where('jet_product_id = \'' . pSQL($jet_product_id) . '\'');

        if (Db::getInstance()->getValue($sql) == 0) {
            $row = array(
                'jet_product_id' => pSQL($jet_product_id),
                'jet_product_percent' => (float)$jet_product_percent,
                'jet_product_meseci' => pSQL($jet_product_meseci),
                'jet_product_price' => (float)$jet_product_price,
                'jet_product_start' => pSQL($jet_product_start),
                'jet_product_end' => pSQL($jet_product_end),
            );
            return Db::getInstance()->insert('jet_kop', $row);
        } else {
            return -1;
        }
        return 0;
    }

    public static function deleteByJetProductId($jet_product_id)
    {
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'jet_kop` WHERE `jet_product_id` = \'' . pSQL($jet_product_id) . '\'';
        return Db::getInstance()->execute($sql);
    }

    private static function fetchFirstValue(DbQuery $sql, $field) {
        $db = Db::getInstance();
        $res = $db->query((string)$sql);

        if (!$res) {
            return false;
        }

        if (is_object($res) && method_exists($res, 'fetch_assoc')) {
            $row = $res->fetch_assoc();
            return ($row && array_key_exists($field, $row)) ? $row[$field] : false;
        }

        if (is_object($res) && method_exists($res, 'fetch')) {
            $row = $res->fetch(\PDO::FETCH_ASSOC);
            return ($row && array_key_exists($field, $row)) ? $row[$field] : false;
        }

        if (is_resource($res)) {
            $row = @mysql_fetch_assoc($res);
            return ($row && array_key_exists($field, $row)) ? $row[$field] : false;
        }

        return false;
    }

    public static function getPromo($jet_product_id, $jet_vnoski, $jet_total_credit_price) {
        $jet_card_in = (int) Configuration::get('JET_CARD_IN');
        $jet_purcent = (float) Configuration::get('JET_PURCENT');

        $jet_purcent_card = null;
        if ($jet_card_in === 1) {
            $jet_purcent_card = (float) Configuration::get('JET_PURCENT_CARD');
        }

        $jet_show_button = true;

        $commonWhere = array(
            'FIND_IN_SET(' . (int)$jet_vnoski . ", REPLACE(jet_product_meseci, '_', ','))",
            (float)$jet_total_credit_price . ' >= jet_product_price',
            'jet_product_start <= CURDATE()',
            'jet_product_end >= CURDATE()',
        );

        $sql = new DbQuery();
        $sql->select('jet_product_percent');
        $sql->from('jet_kop');
        $sql->where('jet_product_id = \'' . pSQL($jet_product_id) . '\'');
        foreach ($commonWhere as $w) {
            $sql->where($w);
        }
        $sql->orderBy('id ASC');
        $jet_product_percent = self::fetchFirstValue($sql, 'jet_product_percent');

        if ($jet_product_percent === false) {
            $sql2 = new DbQuery();
            $sql2->select('jet_product_percent');
            $sql2->from('jet_kop');
            $sql2->where('jet_product_id = \'*\'');
            foreach ($commonWhere as $w) {
                $sql2->where($w);
            }
            $sql2->orderBy('id ASC');

            $jet_product_percent = self::fetchFirstValue($sql2, 'jet_product_percent');
        }

        if ($jet_product_percent !== false) {
            if ((float)$jet_product_percent === -1.00) {
                $jet_show_button = false;
            } else {
                $jet_purcent = (float)$jet_product_percent;
                if ($jet_card_in === 1) {
                    $jet_purcent_card = (float)$jet_product_percent;
                }
            }
        }

        return [
            'jet_show_button'  => $jet_show_button,
            'jet_purcent'      => $jet_purcent,
            'jet_purcent_card' => ($jet_card_in === 1) ? $jet_purcent_card : null,
        ];
    }
}
