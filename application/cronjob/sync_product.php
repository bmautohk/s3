<?php
include_once('config.php');

$db = mysql_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);

// Truncate table
mysql_select_db(TARGET_DB_NAME, $db);
$sql = 'truncate table product_master';
mysql_query($sql, $db) or die (mysql_error()."<br />Couldn't execute query: $sql");

// Copy data from source database
$sql = 'INSERT INTO product_master (id, customer, prod_sn, status, no_jp, factory_no, made, model, model_no, year, item_group, material, product_desc, product_desc_ch, product_desc_jp, pcs, colour, colour_no, moq, molding, cost, kaito, other, buy_date, receive_date, supplier, purchase_cost, business_price, auction_price, kaito_price, factory_date, pack_remark, order_date, progress, receive_model_date, person_in_charge, state, ship_date, market_research_price, yahoo_produce, accessory_remark, company_remark, produce_status, is_monopoly, create_date)
select id, customer, prod_sn, status, no_jp, factory_no, made, model, model_no, year, item_group, material, product_desc, product_desc_ch, product_desc_jp, pcs, colour, colour_no, moq, molding, cost, kaito, other, buy_date, receive_date, supplier, purchase_cost, business_price, auction_price, kaito_price, factory_date, pack_remark, order_date, progress, receive_model_date, person_in_charge, state, ship_date, market_research_price, yahoo_produce, accessory_remark, company_remark, produce_status, is_monopoly, create_date
from '.SOURCE_DB_NAME.'.product_master';
mysql_query($sql, $db) or die (mysql_error()."<br />Couldn't execute query: $sql");
