DROP VIEW IF EXISTS view_recharge_gateways;
CREATE VIEW view_recharge_gateways AS
SELECT rch.id, rch.operator_id, rch.primary_gateway_ids, rch.secondary_gateway_ids, rch.state_id,
rch.amount, st.name as 'state_name', opt.name as 'operator_name', api1.name as 'primary_api_name',
api2.name as 'secondary_api_name' FROM recharge_gateways AS rch
LEFT JOIN states AS st ON
    st.id = rch.state_id
LEFT JOIN operators AS opt ON
    opt.id = rch.operator_id
LEFT JOIN api_gateways AS api1 ON
    api1.id = rch.primary_gateway_ids
LEFT JOIN api_gateways AS api2 ON
    api2.id = rch.secondary_gateway_ids
WHERE rch.is_active = 1;


DROP VIEW IF EXISTS view_users;
CREATE VIEW view_users AS
SELECT usr.id, usr.role_id, usr.parent_id, usr.distributor_id, usr.subdistributor_id, usr.user_identifier, usr.name, rle.name as 'role_name',
 par.name as 'parent_name', usr.username as 'login_id', usr.mobile, usr.email, usr.profile_image, usr.address,
 usr.deposit_amount, usr.subdistributor_fees, usr.retailer_fees, usr.agency_name, usr.client_limit, usr.api_token,
 usr.retailer_margin_limit, usr.user_status, usr.allowed_device, usr.allowed_ip, ub.recharge_balance, ub.payment_balance FROM users AS usr
 LEFT JOIN roles AS rle ON
    rle.id = usr.role_id
LEFT JOIN users AS par ON
    par.id = usr.parent_id
LEFT JOIN user_balances AS ub ON
    ub.user_id = usr.id
WHERE usr.is_active = 1 AND rle.is_active = 1 AND par.is_active = 1 ORDER BY usr.id ASC;


DROP VIEW IF EXISTS view_operators;
CREATE VIEW view_operators AS
SELECT opt.id, opt.recharge_code, opt.topup_code, opt.category_id, opt.name, opt.wallet_type_id,
 opt.default_type, opt.minimum_rech_amt, opt.maximum_rech_amt, opt.invalid_amount,opt.reason_desc,
 opt.starting_number, opt.process_number_length, opt.recharge_denomination, opt.recharge_check, opt.topup_denomination,
 opt.topup_check, opt.same_no_same_amt_delay, opt.same_no_diff_amt_delay, opt.status, wtype.name as 'wtype_name',
 cat.name as 'category_name' FROM operators AS opt
 LEFT JOIN wallet_types AS wtype ON
    wtype.id = opt.wallet_type_id
LEFT JOIN categories AS cat ON
    cat.id = opt.category_id
WHERE opt.is_active = 1 AND cat.is_active = 1 AND wtype.is_active = 1;


DROP VIEW IF EXISTS view_user_margins;
CREATE VIEW view_user_margins AS
SELECT um.id, um.user_id, um.from_amount, um.to_amount, um.commission, usr.id as 'usr_id', usr.role_id, usr.name, usr.user_identifier, usr.mobile FROM user_margins AS um
LEFT JOIN users AS usr ON
    usr.id = um.user_id
WHERE usr.is_active = 1 AND um.is_active = 1;


DROP VIEW IF EXISTS view_payments;
CREATE VIEW view_payments AS
SELECT pay.*, fuser.name as 'from_user_name', fuser.user_identifier as 'from_user_identifier',tuser.name as 'to_user_name',
tuser.user_identifier as 'to_user_identifier', wtype.name as 'wallet_type', pmode.name as 'payment_mode',
CASE
   WHEN pay.payment_type = 1 THEN 'Credit'
   WHEN pay.payment_type = 2 THEN 'Debit'
   ELSE 'Nothing'
END as 'payment_type_name'
FROM payments AS pay
LEFT JOIN users AS fuser ON
   fuser.id = pay.from_user_id
LEFT JOIN users AS tuser ON
   tuser.id = pay.to_user_id
LEFT JOIN wallet_types AS wtype ON
   wtype.id = pay.wallet_type_id
LEFT JOIN payment_modes AS pmode ON
   pmode.id = pay.payment_mode_id
WHERE pay.is_active = 1 AND fuser.is_active = 1 AND tuser.is_active = 1;


DROP VIEW IF EXISTS view_transactions;
CREATE VIEW view_transactions AS
SELECT trans.*, user.name as 'user_name', user.user_identifier,wtype.name as 'wallet_type',rch.process_number,
rch.operator_name,rch.operator_id,
CASE
   WHEN trans.reference_table = 'recharges' THEN rch.req_status
   WHEN trans.reference_table = 'payments' THEN pay.pay_status
   WHEN trans.reference_table = 'users' THEN 3
   ELSE '0'
END as 'req_status', 
CASE
   WHEN trans.transaction_type = 1 THEN 'Credit'
   WHEN trans.transaction_type = 2 THEN 'Debit'
   ELSE 'Nothing'
END as 'payment_type', 
CASE
   WHEN trans.reference_table = 'recharges' THEN rch.recharge_type
   WHEN trans.reference_table = 'payments' THEN 'Payments'
   WHEN trans.reference_table = 'users' THEN 'User Creation'
   ELSE 'Nothing'
END as 'action_type',
CASE
   WHEN trans.reference_table = 'recharges' THEN rch.request_id
   WHEN trans.reference_table = 'payments' THEN pay.request_id
   WHEN trans.reference_table = 'users' THEN user.id
   ELSE 'Nothing'
END as 'request_id',
CASE
   WHEN trans.reference_table = 'recharges' THEN rch.response_datetime
   WHEN trans.reference_table = 'payments' THEN pay.updated_at
   WHEN trans.reference_table = 'users' THEN user.created_at
   ELSE 'Nothing'
END as 'response_datetime',
CASE
   WHEN trans.reference_table = 'recharges' THEN rch.transaction_ids
   WHEN trans.reference_table = 'payments' THEN '-'
   WHEN trans.reference_table = 'users' THEN '-'
   ELSE 'Nothing'
END as 'transaction_ids',
CASE
   WHEN trans.reference_table = 'recharges' THEN rch.process_amount
   WHEN trans.reference_table = 'payments' THEN pay.payment_amount
   WHEN trans.reference_table = 'users' THEN trans.amount
   ELSE '0.00'
END as 'process_amount',
CASE
   WHEN trans.reference_table = 'recharges' THEN rch.commission_amount
   WHEN trans.reference_table = 'payments' THEN pay.commission_amount
   WHEN trans.reference_table = 'users' THEN '0.00'
   ELSE '0.00'
END as 'commission_amount',
CASE
   WHEN trans.reference_table = 'recharges' THEN rch.request_from
   WHEN trans.reference_table = 'payments' THEN pay.request_from
   WHEN trans.reference_table = 'users' THEN 'WEB'
   ELSE '0.00'
END as 'request_from',
CASE
   WHEN trans.reference_table = 'recharges' THEN CONCAT(rch.request_from, '', rch.request_id) 
   WHEN trans.reference_table = 'payments' THEN CONCAT(pay.request_from, '', pay.request_id)
   WHEN trans.reference_table = 'users' THEN CONCAT('USR','',trans.reference_id)
   ELSE '-'
END as 'request_ids'
FROM transactions AS trans
LEFT JOIN users AS user ON
   user.id = trans.user_id
LEFT JOIN wallet_types AS wtype ON
   wtype.id = trans.wallet_type_id
LEFT JOIN recharges AS rch ON
   rch.id = trans.reference_id AND trans.reference_table = 'recharges' AND rch.is_active = 1
LEFT JOIN payments AS pay ON
   pay.id = trans.reference_id AND trans.reference_table = 'payments' AND pay.is_active = 1
WHERE trans.is_active = 1 AND user.is_active = 1  order by id ASC;

DROP VIEW IF EXISTS view_recharges;
CREATE VIEW view_recharges AS
SELECT rech.id, rech.request_id,rech.user_id, rech.process_number, rech.process_amount, rech.commission_amount, rech.total_amount, rech.operator_name, rech.operator_id, rech.recharge_type, rech.request_datetime,rech.response_datetime,rech.transaction_ids, rech.opening_balance,rech.closing_balance, rech.req_status, rech.request_from, rech.request_date, rech.order_id, user.name as 'user_name', user.user_identifier, CONCAT(rech.request_from, '', rech.request_id) as 'request_ids', rech.is_active
FROM recharges AS rech
LEFT JOIN users AS user ON
   user.id = rech.user_id
WHERE rech.is_active = 1 AND user.is_active = 1 AND rech.created_at >= DATE_ADD(CURDATE(), INTERVAL -30 DAY) order by rech.id ASC;

DROP VIEW IF EXISTS view_recharge_permissions;
CREATE VIEW view_recharge_permissions AS
SELECT rech.id, rech.operator_id, rech.user_id, rech.roaming_limit, rech.recharge_limit, rech.minimum_charges, rech.network_commission, rech.is_checked, rech.pay_type, rech.created_by, rech.created_at, user.name as 'user_name', user.user_identifier, ope.name as 'operator_name'
FROM recharge_permissions AS rech
LEFT JOIN users AS user ON
   user.id = rech.user_id
LEFT JOIN operators AS ope ON
   ope.id = rech.operator_id
WHERE user.is_active = 1 AND rech.is_active = 1 AND ope.is_active = 1 order by id ASC;


DROP VIEW IF EXISTS view_user_balances;
CREATE VIEW view_user_balances AS
SELECT ub.id, ub.user_id, ub.recharge_balance, ub.payment_balance, ub.available_balance, usr.id as 'usr_id', usr.name, usr.user_identifier, usr.mobile, usr.money_request FROM user_balances AS ub
LEFT JOIN users AS usr ON
    usr.id = ub.user_id
WHERE usr.is_active = 1;


DROP VIEW IF EXISTS view_menu_rolelists;
CREATE VIEW view_menu_rolelists AS
SELECT rl.id, rl.role_id, rl.is_checked, rl.menu_list_id, ml.name, ml.menu_link, ml.icon_name, ml.menu_order, r.name as 'role_name' FROM menu_role_lists AS rl
LEFT JOIN menu_lists AS ml ON
    ml.id = rl.menu_list_id
LEFT JOIN roles AS r ON
    r.id = rl.role_id
WHERE ml.is_active = 1 AND r.is_active = 1;


DROP VIEW IF EXISTS view_menu_roleactions;
CREATE VIEW view_menu_roleactions AS
SELECT ra.id, ra.role_id, ra.is_checked, ra.menu_action_id, ma.action_name, ma.action_code,  r.name as 'role_name', ml.name as 'menu_name', ml.menu_link FROM menu_role_actions AS ra
LEFT JOIN menu_actions AS ma ON
    ma.id = ra.menu_action_id
LEFT JOIN menu_lists AS ml ON
    ml.id = ma.menu_list_id
LEFT JOIN roles AS r ON
    r.id = ra.role_id
WHERE ml.is_active = 1 AND r.is_active = 1;


DROP VIEW IF EXISTS view_payment_reports;
CREATE VIEW view_payment_reports AS
SELECT trans.id as 'transaction_id', pay.id as 'payment_id', trans.user_id, pay.payment_date, pay.payment_amount, pay.commission_percentage, pay.extra_percentage, pay.commission_amount, pay.total_amount, trans.opening_balance,trans.closing_balance, pay.payment_mode_id, pay.is_rollbacked, pmode.name as 'payment_mode', pay.wallet_type_id, wtype.name as 'wallet_type',user.name as 'user_name', user.user_identifier, trans.transaction_type,
CASE
   WHEN trans.transaction_type = 1 THEN 'Credit'
   WHEN trans.transaction_type = 2 THEN 'Debit'
   ELSE 'Nothing'
END as 'transaction_type_name', pay.remarks, trans.descriptions
FROM transactions AS trans
LEFT JOIN payments AS pay ON
   pay.id = trans.reference_id 
LEFT JOIN users AS user ON
   user.id = trans.user_id
LEFT JOIN wallet_types AS wtype ON
   wtype.id = trans.wallet_type_id
LEFT JOIN payment_modes AS pmode ON
   pmode.id = pay.payment_mode_id
WHERE trans.is_active = 1 AND trans.reference_table = 'payments' AND user.is_active = 1 order by trans.id ASC;


DROP VIEW IF EXISTS view_current_recharges;
CREATE VIEW view_current_recharges AS
SELECT rech.id, rech.request_id,rech.user_id, rech.process_number, rech.process_amount, rech.commission_amount, rech.total_amount, rech.operator_name, rech.operator_id, rech.recharge_type, rech.request_datetime,rech.response_datetime,rech.transaction_ids, rech.opening_balance,rech.closing_balance, rech.req_status, rech.request_from, rech.request_date, user.name as 'user_name', gatw.name as 'api_name', rech.api_response, rech.api_id, user.user_identifier, rech.is_active
FROM recharges AS rech
LEFT JOIN users AS user ON
   user.id = rech.user_id
LEFT JOIN api_gateways AS gatw ON 
   gatw.id = rech.api_id
WHERE user.is_active = 1 AND rech.is_active = 1 AND rech.req_status IN(1,2) AND rech.created_at >= DATE_ADD(CURDATE(), INTERVAL -15 DAY) order by id ASC;


DROP VIEW IF EXISTS view_refill_limits;
CREATE VIEW view_refill_limits AS
SELECT rl.id, rl.user_id, rl.parent_id, rl.daily_limit, rl.average_amt, rl.is_status, rl.action_date, usr.name, 
usr.user_identifier, usr.mobile, usr.role_id, pusr.name as 'parent_name', pusr.user_identifier as 'parent_identifier'  FROM refill_limits AS rl
LEFT JOIN users AS usr ON
    usr.id = rl.user_id
LEFT JOIN users AS pusr ON
    pusr.id = usr.parent_id
WHERE usr.is_active = 1 AND rl.is_active = 1;


DROP VIEW IF EXISTS view_complaints;
CREATE VIEW view_complaints AS
SELECT com.id, com.request_ids,com.user_id, com.complaint_type, com.correct_number, com.description, com.action_status,com.recharge_id, 
com.created_at,rech.process_number, rech.process_amount, rech.operator_name,rech.operator_id,rech.request_datetime as 'recharge_datetime', rech.transaction_ids, 
rech.req_status, rech.user_name,rech.user_identifier,usr.name as 'assigned_user', usr.user_identifier as 'assigned_identifier'
FROM complaints AS com
LEFT JOIN view_recharges AS rech ON
   rech.id = com.recharge_id
LEFT JOIN view_users AS usr ON
   usr.id = com.assigned_to
WHERE com.is_active = 1 order by com.id ASC;


DROP VIEW IF EXISTS view_profit_loss_sheets;
CREATE VIEW view_profit_loss_sheets AS
SELECT pl.id, pl.user_id, pl.opening_balance, pl.closing_balance, pl.purchase_amount, pl.sales_amount, pl.action_date, usr.name, 
usr.user_identifier, usr.mobile, usr.role_id, usr.parent_id, pusr.name as 'parent_name', pusr.user_identifier as 'parent_identifier' FROM profit_loss_sheets AS pl
LEFT JOIN users AS usr ON
    usr.id = pl.user_id
LEFT JOIN users AS pusr ON
    pusr.id = usr.parent_id
WHERE usr.is_active = 1 AND pl.is_active = 1 AND pl.created_at >= DATE_ADD(CURDATE(), INTERVAL -15 DAY) order by id ASC;

/*

utf8mb4_0900_ai_ci

utf8mb4_unicode_ci

php artisan route:list

php artisan make:migration view_user_margins

php artisan migrate --path='./database/migrations/2022_05_06_075659_view_user_margins.php'

php artisan make:model ViewComplaint
php artisan make:model ViewRefillLimit
php artisan make:model ViewCurrentRecharge
php artisan make:model ApiResponse -mcr
php artisan make:model RefillLimit -mcr
php artisan make:migration create_tablename_table
php artisan migrate
php artisan make:model Tablename(Students)
php artisan make:controller Controller(StudentCcontroller) --resource 

Create Cron File 
------------------------
php artisan make:command EcwCron --command=ecw:cron

Execute Cron 
-----------------
php artisan schedule:run


/var/www/html/raghu/lcrud/app/Http/Controllers/

/var/www/html/raghu/lcrud/app/

/var/www/html/raghu/lcrud/app/routes/

/var/www/html/raghu/lcrud/resources/views/

*/


