/*
* 1 InsertOperatorCodeForNewApiGateway
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `InsertOperatorCodeForNewApiGateway` $$
CREATE TRIGGER `InsertOperatorCodeForNewApiGateway` AFTER INSERT ON `api_gateways`
    FOR EACH ROW BEGIN
	INSERT INTO operator_codes (api_gateway_id, operator_id, created_by, created_at, updated_by, updated_at)
	SELECT  NEW.id, id, NEW.created_by, NEW.created_at, NEW.updated_by, NEW.updated_at FROM operators WHERE is_active = 1;
END $$
DELIMITER $$

/*
* 2 DisableOperatorCodeAfterDeleteApiGateway
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `DisableOperatorCodeAfterDeleteApiGateway` $$
CREATE TRIGGER `DisableOperatorCodeAfterDeleteApiGateway` AFTER UPDATE ON `api_gateways`
    FOR EACH ROW BEGIN
	UPDATE operator_codes SET is_active = 0, updated_by = NEW.updated_by, updated_at = NEW.updated_at WHERE api_gateway_id = NEW.id AND NEW.is_active = 0;
END $$
DELIMITER $$

/*
* 3 InsertOperatorCodeForNewOperator
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `InsertOperatorCodeForNewOperator` $$
CREATE TRIGGER `InsertOperatorCodeForNewOperator` AFTER INSERT ON `operators`
    FOR EACH ROW BEGIN
	INSERT INTO operator_codes (api_gateway_id, operator_id, created_by, created_at, updated_by, updated_at)
	SELECT  id, NEW.id, NEW.created_by, NEW.created_at, NEW.updated_by, NEW.updated_at FROM api_gateways WHERE is_active = 1;
END $$
DELIMITER $$

/*
* 4 InsertRechargeGatewayForNewOperator
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `InsertRechargeGatewayForNewOperator` $$
CREATE TRIGGER `InsertRechargeGatewayForNewOperator` AFTER INSERT ON `operators`
    FOR EACH ROW BEGIN
	INSERT INTO recharge_gateways (operator_id, state_id, created_by, created_at, updated_by, updated_at)
	SELECT   NEW.id, id, NEW.created_by, NEW.created_at, NEW.updated_by, NEW.updated_at FROM states WHERE is_active = 1;
END $$
DELIMITER $$

/*
* 5 DisableOperatorCodeAfterDeleteOperator
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `DisableOperatorCodeAfterDeleteOperator` $$
CREATE TRIGGER `DisableOperatorCodeAfterDeleteOperator` AFTER UPDATE ON `operators`
    FOR EACH ROW BEGIN
	UPDATE operator_codes SET is_active = 0, updated_by = NEW.updated_by, updated_at = NEW.updated_at WHERE operator_id = NEW.id AND NEW.is_active = 0;
END $$
DELIMITER $$

/*
* 6 DisableRechargeGatewayAfterDeleteOperator
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `DisableRechargeGatewayAfterDeleteOperator` $$
CREATE TRIGGER `DisableRechargeGatewayAfterDeleteOperator` AFTER UPDATE ON `operators`
    FOR EACH ROW BEGIN
	UPDATE recharge_gateways SET is_active = 0, updated_by = NEW.updated_by, updated_at = NEW.updated_at WHERE operator_id = NEW.id AND NEW.is_active = 0;
END $$
DELIMITER $$



/*
* 7 InsertRechargeGatewayForNewState
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `InsertRechargeGatewayForNewState` $$
CREATE TRIGGER `InsertRechargeGatewayForNewState` AFTER INSERT ON `states`
    FOR EACH ROW BEGIN
	INSERT INTO recharge_gateways (operator_id, state_id, created_by, created_at, updated_by, updated_at)
	SELECT   id, New.id, NEW.created_by, NEW.created_at, NEW.updated_by, NEW.updated_at FROM operators WHERE is_active = 1;
END $$
DELIMITER $$

/*
* 8 DisableRechargeGatewayAfterDeleteState
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `DisableRechargeGatewayAfterDeleteState` $$
CREATE TRIGGER `DisableRechargeGatewayAfterDeleteState` AFTER UPDATE ON `states`
    FOR EACH ROW BEGIN
	UPDATE recharge_gateways SET is_active = 0, updated_by = NEW.updated_by, updated_at = NEW.updated_at WHERE state_id = NEW.id AND is_active = 0;
END $$
DELIMITER $$



/*
* 9 InsertUserMarginForNewUser
*/

DELIMITER $$
DROP TRIGGER IF EXISTS `InsertRchPermissionForNewUser` $$
CREATE TRIGGER `InsertRchPermissionForNewUser` AFTER INSERT ON `users`
    FOR EACH ROW BEGIN
	INSERT INTO recharge_permissions (operator_id, user_id, roaming_limit, recharge_limit, minimum_charges, network_commission, is_checked, pay_type, created_by, created_at, updated_by, updated_at)
	SELECT operator_id, NEW.id, roaming_limit, recharge_limit, minimum_charges, network_commission, is_checked, pay_type, NEW.created_by, NEW.created_at, NEW.updated_by, NEW.updated_at  FROM recharge_permissions WHERE user_id = NEW.parent_id;
END $$
DELIMITER $$

/*
* 10 InsertUserMarginForNewUser
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `InsertUserMarginForNewUser` $$
CREATE TRIGGER `InsertUserMarginForNewUser` AFTER INSERT ON `users`
    FOR EACH ROW BEGIN
	INSERT INTO user_margins (user_id, created_by, created_at, updated_by, updated_at)
	SELECT New.id, NEW.created_by, NEW.created_at, NEW.updated_by, NEW.updated_at FROM users WHERE id = New.id AND is_active = 1;
END $$
DELIMITER $$

/*
* 11 InsertUserBalanceForNewUser
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `InsertUserBalanceForNewUser` $$
CREATE TRIGGER `InsertUserBalanceForNewUser` AFTER INSERT ON `users`
    FOR EACH ROW BEGIN
	INSERT INTO user_balances (user_id, created_by, created_at, updated_by, updated_at)
	SELECT New.id, NEW.created_by, NEW.created_at, NEW.updated_by, NEW.updated_at FROM users WHERE id = New.id AND is_active = 1;
END $$
DELIMITER $$


/*
* 12 InsertMenuRoleWiseForNewMenu
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `InsertMenuRoleWiseForNewMenu` $$
CREATE TRIGGER `InsertMenuRoleWiseForNewMenu` AFTER INSERT ON `menu_lists`
    FOR EACH ROW BEGIN
	INSERT INTO menu_role_lists (menu_list_id, role_id, created_by, created_at, updated_by, updated_at)
	SELECT  NEW.id, id, NEW.created_by, NEW.created_at, NEW.updated_by, NEW.updated_at FROM roles WHERE is_active = 1;
END $$
DELIMITER $$


/*
* 13 InsertMenuRoleActionForNewAction
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `InsertMenuRoleActionForNewAction` $$
CREATE TRIGGER `InsertMenuRoleActionForNewAction` AFTER INSERT ON `menu_actions`
    FOR EACH ROW BEGIN
	INSERT INTO menu_role_actions (menu_action_id, role_id, created_by, created_at, updated_by, updated_at)
	SELECT  NEW.id, id, NEW.created_by, NEW.created_at, NEW.updated_by, NEW.updated_at FROM roles WHERE is_active = 1;
END $$
DELIMITER $$


/*
* 14 InsertUserRefillLimitForNewUser
*/
DELIMITER $$
DROP TRIGGER IF EXISTS `InsertUserRefillLimitForNewUser` $$
CREATE TRIGGER `InsertUserRefillLimitForNewUser` AFTER INSERT ON `users`
    FOR EACH ROW BEGIN
	INSERT INTO refill_limits (user_id, parent_id, action_date, created_by, created_at, updated_by, updated_at)
	SELECT New.id, NEW.parent_id, CURDATE(), NEW.created_by, NEW.created_at, NEW.updated_by, NEW.updated_at FROM users WHERE role_id IN(2,3,4) AND id = New.id AND is_active = 1;
END $$
DELIMITER $$


