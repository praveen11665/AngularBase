/****
	In Query files developer to be add any query to be place it.The files are moved to live check the queries file with date wised.		
*****/
//Master Query  06/10/2018

ALTER TABLE `cities` ADD `created_on` DATETIME NOT NULL AFTER `state_id`, ADD `created_by` INT UNSIGNED NOT NULL AFTER `created_on`, ADD `updated_on` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_by`, ADD `updated_by` INT UNSIGNED NOT NULL AFTER `updated_on`, ADD `is_delete` TINYINT NOT NULL DEFAULT '0' AFTER `updated_by`, ADD INDEX (`updated_by`), ADD INDEX (`created_by`)

ALTER TABLE `states` ADD `created_on` DATETIME NOT NULL AFTER `country_id`, ADD `created_by` INT UNSIGNED NOT NULL AFTER `created_on`, ADD `updated_on` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_by`, ADD `updated_by` INT UNSIGNED NOT NULL AFTER `updated_on`, ADD `is_delete` TINYINT NOT NULL DEFAULT '0' AFTER `updated_by`, ADD INDEX (`updated_by`), ADD INDEX (`created_by`)


ALTER TABLE `countries` ADD `created_on` DATETIME NOT NULL AFTER `isd_code`, ADD `created_by` INT UNSIGNED NOT NULL AFTER `created_on`, ADD `updated_on` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `created_by`, ADD `updated_by` INT UNSIGNED NOT NULL AFTER `updated_on`, ADD `is_delete` TINYINT NOT NULL DEFAULT '0' AFTER `updated_by`, ADD INDEX (`updated_by`), ADD INDEX (`created_by`)

ALTER TABLE `countries` ADD `status` TINYINT NOT NULL DEFAULT '0' AFTER `isd_code`;
ALTER TABLE `states` ADD `status` TINYINT NOT NULL DEFAULT '0' AFTER `country_id`;
ALTER TABLE `cities` ADD `status` TINYINT NOT NULL DEFAULT '0' AFTER `state_id`;


--------------------------------------------------------------08-10-2018-------------------------------------------------------------

ALTER TABLE `document` CHANGE `document_for` `document_for` VARCHAR(150) NOT NULL;
INSERT INTO `app_roles` (`role_id`, `role_name`) VALUES (NULL, 'Buyer'), (NULL, 'Seller');

/**************************************08-08-2018*****************************************************/

CREATE TABLE `def_modules` (
  `modules_id` int(10) UNSIGNED NOT NULL,
  `module_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `def_modules` (`modules_id`, `module_name`) VALUES
(1, 'General'),
(2, 'Registration'),
(3, 'Deal Posting'),
(4, 'Deal Notification'),
(5, 'Deal Request'),
(6, 'Transaction complete');

ALTER TABLE `def_modules`
  ADD PRIMARY KEY (`modules_id`);

ALTER TABLE `def_modules`
MODIFY `modules_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

CREATE TABLE `notification` (
  `notify_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `modules_id` int(10) UNSIGNED NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int(10) UNSIGNED NOT NULL,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `notification`
  ADD PRIMARY KEY (`notify_id`),
  ADD KEY `modules_id` (`modules_id`);

ALTER TABLE `notification`
  MODIFY `notify_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`modules_id`) REFERENCES `def_modules` (`modules_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

  UPDATE `countries` SET `status` = '1'
  UPDATE `states` SET `status` = '1';
  UPDATE `cities` SET `status` = '1';

/********************************09-10-2018(praveen)***************************/

ALTER TABLE `user_profile` ADD `profile_status` TINYINT NOT NULL DEFAULT '0' AFTER `anniversary`;

TRUNCATE app_roles
INSERT INTO `app_roles` (`role_id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'User'),
(3, 'Vendor'),
(4, 'Seller'),
(5, 'Buyer');

ALTER TABLE `user_profile` ADD `document_status` TINYINT NULL DEFAULT '0' AFTER `profile_status`, ADD `approved_status` TINYINT NOT NULL DEFAULT '0' AFTER `document_status`;

/******************************************************10-10-2018***************************************/

ALTER TABLE `document` ADD `document_type` VARCHAR(150) NOT NULL AFTER `document_for`;

CREATE TABLE `document_type` (
  `doc_type_id` int(10) UNSIGNED NOT NULL,
  `document_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `document_type` (`doc_type_id`, `document_type`) VALUES
(1, 'Images'),
(2, 'PDF'),
(3, 'Word');

ALTER TABLE `document_type`
  ADD PRIMARY KEY (`doc_type_id`);

ALTER TABLE `document_type`
  MODIFY `doc_type_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

UPDATE `configuration` SET `value` = 'czoyMToiTG9vcGhvbGUgVGVjaG5vbG9naWVzIjs=' WHERE `configuration`.`conf_id` = 1;

/***************************************11-10-2018************************************************/
ALTER TABLE `user_profile` ADD `user_device` TEXT NOT NULL AFTER `anniversary`;

CREATE TABLE `product` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_name` varchar(50) NOT NULL,
  `tbt_cut_points` varchar(50) NOT NULL,
  `density` varchar(50) NOT NULL,
  `viscosity` varchar(50) NOT NULL,
  `user_agreement_terms` longtext NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_on` datetime NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `product`
  MODIFY `product_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

CREATE TABLE `disapprove_reasons` (
  `reasons_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `reason` text NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `push_notifications` (
  `push_notify_id` int(10) UNSIGNED NOT NULL,
  `notify_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `view_status` tinyint(4) NOT NULL DEFAULT '0',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `disapprove_reasons`
  ADD PRIMARY KEY (`reasons_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `push_notifications`
  ADD PRIMARY KEY (`push_notify_id`),
  ADD KEY `notify_id` (`notify_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `disapprove_reasons`
  MODIFY `reasons_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `push_notifications`
  MODIFY `push_notify_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `disapprove_reasons`
  ADD CONSTRAINT `disapprove_reasons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE `push_notifications`
  ADD CONSTRAINT `push_notifications_ibfk_1` FOREIGN KEY (`notify_id`) REFERENCES `notification` (`notify_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `push_notifications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `product` ADD `is_delete` BINARY(10) NOT NULL AFTER `updated_on`;
ALTER TABLE `product` CHANGE `is_delete` `is_delete` TINYINT NOT NULL DEFAULT '0';

/***************************************12-10-2018************************************************/
ALTER TABLE `product` ADD `approved_status` TINYINT NOT NULL DEFAULT '0' AFTER `is_delete`;

CREATE TABLE `deals_reasons` (
  `reasons_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `reason` text NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `deals_reasons`
  ADD PRIMARY KEY (`reasons_id`),
  ADD KEY `product_id` (`product_id`);

ALTER TABLE `deals_reasons`
  MODIFY `reasons_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `deals_reasons`
  ADD CONSTRAINT `deals_reasons_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*************************17-10-2018 ********************************/

CREATE TABLE `registration_status` (
  `reg_id` int(10) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `registration_status` (`reg_id`, `status`) VALUES
(0, 'New'),
(1, 'Approved'),
(2, 'Disapproved');

ALTER TABLE `registration_status`
  ADD PRIMARY KEY (`reg_id`);

ALTER TABLE `registration_status`
  MODIFY `reg_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `user_profile` CHANGE `approved_status` `approved_status` INT UNSIGNED NOT NULL;
ALTER TABLE `user_profile` ADD INDEX(`approved_status`)
ALTER TABLE `user_profile` ADD FOREIGN KEY (`approved_status`) REFERENCES `registration_status`(`reg_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/********************************19-10-2018 ****************************/

CREATE TABLE `activity_logs` (
  `log_id` int(10) UNSIGNED NOT NULL,
  `log_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activity_id` int(10) UNSIGNED NOT NULL,
  `log_activity` text NOT NULL,
  `log_activity_link` text NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `fixed_actitivity` (
  `activity_id` int(10) UNSIGNED NOT NULL,
  `activity_type` varchar(255) NOT NULL,
  `activity_icon` varchar(50) NOT NULL,
  `activity_class` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `fixed_actitivity` (`activity_id`, `activity_type`, `activity_icon`, `activity_class`) VALUES
(1, 'Created', 'mdi-account-check', 'success'),
(2, 'Updated', 'mdi-arrow-up-bold-hexagon-outline', 'warning'),
(3, 'Uploaded', 'mdi-folder-upload', 'primary'),
(4, 'Deleted', 'mdi-delete', 'danger'),
(5, 'Registration', 'mdi-account-star', 'success'),
(6, 'Approved', 'mdi-thumb-up', 'primary'),
(7, 'Disapproved', 'mdi-thumb-down', 'danger'),
(8, 'Notification', ' mdi-bell-ring', 'primary'),
(9, 'Email', 'mdi-email-open', 'primary'),
(10, 'SMS', 'mdi-comment-processing-outline', 'primary'),
(11, 'Login', 'mdi-login', 'primary');

ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `lead_id` (`activity_id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `fixed_actitivity`
  ADD PRIMARY KEY (`activity_id`);

ALTER TABLE `activity_logs`
  MODIFY `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `fixed_actitivity`
  MODIFY `activity_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `fixed_actitivity` (`activity_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/**************************20-10-18***************************************/

CREATE TABLE `deal_request` (
  `deal_request_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `deal_date` datetime NOT NULL,
  `buyer_id` int(10) UNSIGNED NOT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL,
  `request_date` datetime NOT NULL,
  `reason` text NOT NULL,
  `status` int(10) UNSIGNED NOT NULL,
  `notification_status` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `deal_request`
  ADD PRIMARY KEY (`deal_request_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `status` (`status`);
ALTER TABLE `deal_request`
  MODIFY `deal_request_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `deal_request`
  ADD CONSTRAINT `deal_request_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `deal_request_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `deal_request_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `deal_request_ibfk_4` FOREIGN KEY (`status`) REFERENCES `registration_status` (`reg_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/************************ 22-10-2018********************************/

ALTER TABLE `push_notifications` ADD `title` TEXT NOT NULL AFTER `notify_id`, ADD `content` TEXT NOT NULL AFTER `title`;
UPDATE `def_modules` SET `module_name` = 'Deal Approve Request' WHERE `def_modules`.`modules_id` = 6;
INSERT INTO `def_modules` (`modules_id`, `module_name`) VALUES ('7', 'Deal Disapprove request'), ('8', 'Transaction complete');

/**************************24-10-2018***********************************/

INSERT INTO `registration_status` (`reg_id`, `status`) VALUES ('3', 'Processing'), ('4', 'Waiting');
INSERT INTO `registration_status` (`reg_id`, `status`) VALUES ('5', 'Completed');