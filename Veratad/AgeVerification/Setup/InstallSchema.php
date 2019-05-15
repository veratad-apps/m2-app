<?php

        namespace Veratad\AgeVerification\Setup;

        use Magento\Framework\Setup\InstallSchemaInterface;
        use Magento\Framework\Setup\SchemaSetupInterface;
        use Magento\Framework\Setup\ModuleContextInterface;

        class InstallSchema implements InstallSchemaInterface
        {
            public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
            {
                $setup->startSetup();

                $table = $setup->getConnection()->newTable(
                    $setup->getTable('veratad_history')
                )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Primary Key ID'
                )
                ->addColumn(
                    'veratad_action',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Action'
                )
                ->addColumn(
                    'veratad_order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Order ID'
                )
                ->addColumn(
                    'veratad_timestamp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Timestamp'
                )
                ->addColumn(
                    'veratad_detail',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Detail'
                )
                ->addColumn(
                    'veratad_confirmation',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Confirmation'
                )
                ->addColumn(
                    'veratad_override',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Manual Override'
                )
                ->addColumn(
                    'veratad_override_user',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad User for Manual Override'
                )
                ->addColumn(
                    'veratad_customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Customer ID'
                )
                ->addColumn(
                    'veratad_address_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Address Type'
                )
                ->setComment(
                    'Veratad History Table'
                );
                $setup->getConnection()->createTable($table);

                $setup->endSetup();

                $setup->startSetup();

                $table = $setup->getConnection()->newTable(
                    $setup->getTable('veratad_account')
                )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Primary Key ID'
                )
                ->addColumn(
                    'veratad_customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    255,
                    [],
                    'Customer ID for Veratad Transaction'
                )
                ->addColumn(
                    'veratad_confirmation',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    255,
                    [],
                    'Veratad Confirmation Number'
                )
                ->addColumn(
                    'veratad_action',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Action'
                )
                ->addColumn(
                    'veratad_detail',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Detail'
                )
                ->addColumn(
                    'veratad_timestamp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Transaction Timestamp'
                )
                ->addColumn(
                    'veratad_fn',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Account First Name'
                )
                ->addColumn(
                    'veratad_ln',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Veratad Account Last Name'
                )
                ->setComment(
                    'Veratad Account Table'
                );
                $setup->getConnection()->createTable($table);

                $setup->endSetup();

                $setup->startSetup();

                $table = $setup->getConnection()->newTable(
                    $setup->getTable('veratad_name_diff')
                )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Primary Key ID'
                )
                ->addColumn(
                    'veratad_quote',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Quote ID for Veratad Transaction'
                )
                ->addColumn(
                    'veratad_billing_fn',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Billing First Name for Veratad Transaction'
                )
                ->addColumn(
                    'veratad_billing_ln',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Billing First Name for Veratad Transaction'
                )
                ->addColumn(
                    'veratad_billing_addr',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Billing Address for Veratad Transaction'
                )
                ->addColumn(
                    'veratad_billing_zip',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Billing Zip for Veratad Transaction'
                )
                ->addColumn(
                    'veratad_shipping_dob',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Shipping DOB for Veratad Transaction'
                )
                ->setComment(
                    'Veratad Billing Name Table'
                );
                $setup->getConnection()->createTable($table);

                $setup->endSetup();
        }
      }
