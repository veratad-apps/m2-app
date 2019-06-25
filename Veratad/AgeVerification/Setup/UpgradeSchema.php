<?php
        namespace Veratad\AgeVerification\Setup;
        use Magento\Framework\Setup\UpgradeSchemaInterface;
        use Magento\Framework\Setup\SchemaSetupInterface;
        use Magento\Framework\Setup\ModuleContextInterface;
        class UpgradeSchema implements UpgradeSchemaInterface
        {
            /**
             * Upgrades DB schema for a module
             *
             * @param SchemaSetupInterface $setup
             * @param ModuleContextInterface $context
             * @return void
             */
            public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
            {
                $setup->startSetup();
                $quoteAddressTable = 'quote_address';
                $orderTable = 'sales_order_address';
                $gridTable = 'sales_order_grid';
                $salesTable = 'sales_order';
                $customerTable = 'customer_entity';
                $quoteTable = 'quote';
                //customer action
                $setup->getConnection()
                    ->addColumn(
                        $setup->getTable($customerTable),
                        'veratad_action',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' =>'Account Age Verification Status',
                            'is_used_in_grid' => true,
                            'is_visible_in_grid' => true,
                            'is_filterable_in_grid' => true,
                            'is_searchable_in_grid' => true,
                            'nullable' => false,
                            'default' => 'NOT VERIFIED'
                        ]
                    );
                    $setup->getConnection()
                        ->addColumn(
                            $setup->getTable($orderTable),
                            'veratad_action',
                            [
                                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                'length' => 255,
                                'comment' =>'Veratad Action'
                            ]
                  );
                  $setup->getConnection()
                      ->addColumn(
                          $setup->getTable($orderTable),
                          'veratad_dob',
                          [
                              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                              'length' => 255,
                              'comment' =>'Veratad DOB'
                          ]
                );
                $setup->getConnection()
                    ->addColumn(
                        $setup->getTable($quoteAddressTable),
                        'veratad_dob',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' =>'Veratad DOB'
                        ]
              );
                $setup->getConnection()
                    ->addColumn(
                        $setup->getTable($quoteTable),
                        'veratad_dob',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' =>'Veratad DOB'
                        ]
              );
                  $setup->getConnection()
                      ->addColumn(
                          $setup->getTable($salesTable),
                          'veratad_action',
                          [
                              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                              'length' => 255,
                              'comment' =>'Veratad Action'
                          ]
                );
                $setup->getConnection()
                    ->addColumn(
                        $setup->getTable($salesTable),
                        'veratad_dob',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 255,
                            'comment' =>'Veratad DOB'
                        ]
              );
                  $setup->getConnection()
                      ->addColumn(
                          $setup->getTable($orderTable),
                          'veratad_confirmation',
                          [
                              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                              'length' => 255,
                              'comment' =>'Veratad Confirmation'
                          ]
                );
                  $setup->getConnection()
                      ->addColumn(
                          $setup->getTable($gridTable),
                          'veratad_action',
                          [
                              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                              'length' => 255,
                              'comment' =>'Veratad Action'
                          ]
                );
                $setup->endSetup();
            }
        }
