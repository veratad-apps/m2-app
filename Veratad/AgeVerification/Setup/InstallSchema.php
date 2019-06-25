<?php

    namespace Veratad\AgeVerification\Setup;

    class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
    {

    	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    	{
    		$installer = $setup;
    		$installer->startSetup();
    		if (!$installer->tableExists('veratad_history')) {
    			$table = $installer->getConnection()->newTable(
    				$installer->getTable('veratad_history')
    			)
    				->addColumn(
    					'veratad_id',
    					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
    					null,
    					[
    						'identity' => true,
    						'nullable' => false,
    						'primary'  => true,
    						'unsigned' => true,
    					],
    					'Veratad ID'
    				)
    				->addColumn(
    					'veratad_action',
    					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    					255,
    					['nullable => false'],
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
    					'veratad_order_id',
    					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    					255,
    					[],
    					'Veratad Order ID'
    				)
            ->addColumn(
    					'veratad_quote_id',
    					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    					255,
    					['nullable => true'],
    					'Veratad Quote ID'
    				)
    				->addColumn(
    					'veratad_confirmation',
    					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
    					255,
    					[],
    					'Veratad Confirmation Number'
    				)
            ->addColumn(
              'veratad_timestamp',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              [],
              'Veratad Timestamp'
            )
            ->addColumn(
              'veratad_dcams_id',
              \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
              255,
              [],
              'Veratad DCAMS ID'
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
    					'User that manual override'
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
            ->addColumn(
            'veratad_id_front',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Veratad DCAMS ID Front Link'
            )
            ->addColumn(
            'veratad_id_back',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Veratad DCAMS ID Back Link'
            )
    				->setComment('Veratad History Table');
    			$installer->getConnection()->createTable($table);

    			$installer->getConnection()->addIndex(
    				$installer->getTable('veratad_history'),
    				$setup->getIdxName(
    					$installer->getTable('veratad_history'),
    					['veratad_order_id', 'veratad_customer_id'],
    					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
    				),
    				['veratad_order_id', 'veratad_customer_id'],
    				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
    			);
    		}
    		$installer->endSetup();
    	}
    }
