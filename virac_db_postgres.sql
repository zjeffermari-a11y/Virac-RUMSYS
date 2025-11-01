/* SQLINES DEMO *** CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/* SQLINES DEMO ***  utf8 */
;
/* SQLINES DEMO ***  utf8mb4 */
;
/* SQLINES DEMO *** TIME_ZONE=@@TIME_ZONE */
;
/* SQLINES DEMO *** ZONE='+00:00' */
;
/* SQLINES DEMO *** FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */
;
/* SQLINES DEMO *** SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */
;
/* SQLINES DEMO *** SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */
;

CREATE DATABASE `capstone_project` /* SQLINES DEMO *** HARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /* SQLINES DEMO *** NCRYPTION='N' */;
/* SET SCHEMA 'capstone_project' */
;

-- SQLINES FOR EVALUATION USE ONLY (14 DAYS)
CREATE TABLE IF NOT EXISTS audit_trails (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    user_id bigint check (user_id > 0) NOT NULL,
    role_id bigint check (role_id > 0) NOT NULL,
    action varchar(100) CHARACTER SET utf8mb4 NOT NULL,
    module varchar(50) CHARACTER SET utf8mb4 NOT NULL,
    result varchar(50) CHARACTER SET utf8mb4 NOT NULL,
    created_at timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT audit_trails_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles (id),
    CONSTRAINT audit_trails_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)
);

ALTER SEQUENCE audit_trails_seq RESTART WITH 69;

CREATE INDEX audit_trails_user_id_foreign ON audit_trails (user_id);

CREATE INDEX audit_trails_role_id_foreign ON audit_trails (role_id);

INSERT INTO
    audit_trails (
        id,
        user_id,
        role_id,
        action,
        module,
        result,
        created_at
    )
VALUES (
        1,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 13:49:33'
    ),
    (
        2,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 13:49:39'
    ),
    (
        3,
        6,
        3,
        'Assigned stall MS-12 to vendor Vendor Twelve',
        'Stall Assignment',
        'Success',
        '2025-10-15 14:03:36'
    ),
    (
        4,
        117,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:11:01'
    ),
    (
        5,
        117,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:11:28'
    ),
    (
        6,
        2,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:11:43'
    ),
    (
        7,
        2,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:13:01'
    ),
    (
        8,
        91,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:13:14'
    ),
    (
        9,
        105,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:14:04'
    ),
    (
        10,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:15:01'
    ),
    (
        11,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:15:09'
    ),
    (
        12,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:23:54'
    ),
    (
        13,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:24:04'
    ),
    (
        14,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:33:31'
    ),
    (
        15,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:35:16'
    ),
    (
        16,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:35:35'
    ),
    (
        17,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:36:27'
    ),
    (
        18,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:40:24'
    ),
    (
        19,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:40:57'
    ),
    (
        20,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:41:19'
    ),
    (
        21,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:42:13'
    ),
    (
        22,
        117,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:42:38'
    ),
    (
        23,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:47:09'
    ),
    (
        24,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:47:32'
    ),
    (
        25,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:47:46'
    ),
    (
        26,
        4,
        4,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:47:57'
    ),
    (
        27,
        2,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:48:11'
    ),
    (
        28,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:48:22'
    ),
    (
        29,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:50:52'
    ),
    (
        30,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:51:03'
    ),
    (
        31,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:52:40'
    ),
    (
        32,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:52:53'
    ),
    (
        33,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:56:18'
    ),
    (
        34,
        4,
        4,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:56:29'
    ),
    (
        35,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 14:57:52'
    ),
    (
        36,
        2,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 15:14:26'
    ),
    (
        37,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 15:17:21'
    ),
    (
        38,
        2,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 15:45:12'
    ),
    (
        39,
        117,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 15:46:44'
    ),
    (
        40,
        117,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 15:47:46'
    ),
    (
        41,
        2,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 15:58:49'
    ),
    (
        42,
        117,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 16:00:35'
    ),
    (
        43,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 20:25:59'
    ),
    (
        44,
        2,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 20:42:52'
    ),
    (
        45,
        118,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 20:43:35'
    ),
    (
        46,
        118,
        2,
        'Completed initial password and username change',
        'Authentication',
        'Success',
        '2025-10-15 20:44:50'
    ),
    (
        47,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 20:49:05'
    ),
    (
        48,
        6,
        3,
        'Assigned stall MS-13 to vendor Vendor Thirteen',
        'Stall Assignment',
        'Success',
        '2025-10-15 20:49:22'
    ),
    (
        49,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 20:49:52'
    ),
    (
        50,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-15 20:55:56'
    ),
    (
        51,
        6,
        3,
        'Generated Monthly Report for October 2025',
        'Reports',
        'Success',
        '2025-10-15 21:05:24'
    ),
    (
        52,
        6,
        3,
        'Generated Monthly Report for October 2025',
        'Reports',
        'Success',
        '2025-10-15 21:24:00'
    ),
    (
        53,
        6,
        3,
        'Generated Monthly Report for October 2025',
        'Reports',
        'Success',
        '2025-10-15 21:27:13'
    ),
    (
        54,
        6,
        3,
        'Downloaded Monthly Report for October 2025',
        'Reports',
        'Success',
        '2025-10-15 21:27:27'
    ),
    (
        55,
        6,
        3,
        'Generated Monthly Report for October 2025',
        'Reports',
        'Success',
        '2025-10-15 21:28:04'
    ),
    (
        56,
        6,
        3,
        'Downloaded Monthly Report for October 2025',
        'Reports',
        'Success',
        '2025-10-15 21:28:13'
    ),
    (
        57,
        2,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:17:52'
    ),
    (
        58,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:21:20'
    ),
    (
        59,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:22:21'
    ),
    (
        60,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:24:16'
    ),
    (
        61,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:29:39'
    ),
    (
        62,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:30:46'
    ),
    (
        63,
        6,
        3,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:31:19'
    ),
    (
        64,
        4,
        4,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:31:53'
    ),
    (
        65,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:33:38'
    ),
    (
        66,
        2,
        2,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:45:20'
    ),
    (
        67,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-16 00:46:19'
    ),
    (
        68,
        1,
        1,
        'User Login',
        'Authentication',
        'Success',
        '2025-10-26 23:45:11'
    );

CREATE TABLE IF NOT EXISTS billing (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    stall_id bigint check (stall_id > 0) NOT NULL,
    utility_type varchar(30) check (
        utility_type in (
            'Rent',
            'Electricity',
            'Water'
        )
    ) CHARACTER SET utf8mb4 NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    amount decimal(10, 2) NOT NULL,
    penalty decimal(10, 2) NOT NULL DEFAULT '0.00',
    amount_after_due decimal(10, 2) DEFAULT NULL,
    previous_reading decimal(10, 2) DEFAULT NULL,
    current_reading decimal(10, 2) DEFAULT NULL,
    consumption decimal(10, 2) DEFAULT NULL,
    rate decimal(10, 4) DEFAULT NULL,
    due_date date NOT NULL,
    disconnection_date date DEFAULT NULL,
    status varchar(30) check (
        status in ('unpaid', 'paid', 'late')
    ) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'unpaid',
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT billing_stall_id_foreign FOREIGN KEY (stall_id) REFERENCES stalls (id)
);

ALTER SEQUENCE billing_seq RESTART WITH 98;

CREATE INDEX billing_stall_id_foreign ON billing (stall_id);

INSERT INTO
    billing (
        id,
        stall_id,
        utility_type,
        period_start,
        period_end,
        amount,
        penalty,
        amount_after_due,
        previous_reading,
        current_reading,
        consumption,
        rate,
        due_date,
        disconnection_date,
        status,
        created_at,
        updated_at
    )
VALUES (
        1,
        1,
        'Rent',
        '2025-09-01',
        '2025-09-30',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-30',
        NULL,
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-09-25 02:33:57'
    ),
    (
        2,
        1,
        'Water',
        '2025-08-01',
        '2025-08-31',
        155.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-13',
        NULL,
        'paid',
        '2025-09-25 02:33:57',
        '2025-10-12 23:51:00'
    ),
    (
        3,
        1,
        'Electricity',
        '2025-08-01',
        '2025-08-31',
        1500.00,
        0.00,
        NULL,
        1500.00,
        1550.00,
        50.00,
        30.0000,
        '2025-09-13',
        '2025-09-19',
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-09-25 02:34:39'
    ),
    (
        4,
        2,
        'Rent',
        '2025-09-01',
        '2025-09-30',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-30',
        NULL,
        'paid',
        '2025-09-25 02:33:57',
        '2025-10-06 11:44:36'
    ),
    (
        5,
        2,
        'Water',
        '2025-08-01',
        '2025-08-31',
        155.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-13',
        NULL,
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-09-25 02:33:57'
    ),
    (
        6,
        2,
        'Electricity',
        '2025-08-01',
        '2025-08-31',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-09-13',
        '2025-09-19',
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-09-25 02:33:57'
    ),
    (
        7,
        16,
        'Rent',
        '2025-09-01',
        '2025-09-30',
        10260.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-30',
        NULL,
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-09-25 02:33:57'
    ),
    (
        8,
        16,
        'Electricity',
        '2025-08-01',
        '2025-08-31',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-09-13',
        '2025-09-19',
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-09-25 02:33:57'
    ),
    (
        9,
        4,
        'Rent',
        '2025-09-01',
        '2025-09-30',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-30',
        NULL,
        'paid',
        '2025-09-25 02:33:57',
        '2025-09-25 21:24:07'
    ),
    (
        10,
        4,
        'Water',
        '2025-08-01',
        '2025-08-31',
        155.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-13',
        NULL,
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-10-02 11:03:22'
    ),
    (
        11,
        4,
        'Electricity',
        '2025-08-01',
        '2025-08-31',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-09-13',
        '2025-09-19',
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-10-02 11:03:22'
    ),
    (
        12,
        17,
        'Rent',
        '2025-09-01',
        '2025-09-30',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-30',
        NULL,
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-10-12 13:15:32'
    ),
    (
        13,
        17,
        'Water',
        '2025-08-01',
        '2025-08-31',
        155.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-13',
        NULL,
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-09-25 02:33:57'
    ),
    (
        14,
        17,
        'Electricity',
        '2025-08-01',
        '2025-08-31',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-09-13',
        '2025-09-19',
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-09-25 02:33:57'
    ),
    (
        15,
        107,
        'Rent',
        '2025-09-01',
        '2025-09-30',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-30',
        NULL,
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-09-25 02:33:57'
    ),
    (
        16,
        107,
        'Water',
        '2025-08-01',
        '2025-08-31',
        155.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-09-13',
        NULL,
        'paid',
        '2025-09-25 02:33:57',
        '2025-10-13 00:04:56'
    ),
    (
        17,
        107,
        'Electricity',
        '2025-08-01',
        '2025-08-31',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-09-13',
        '2025-09-19',
        'unpaid',
        '2025-09-25 02:33:57',
        '2025-09-25 02:33:57'
    ),
    (
        21,
        1,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        1550.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-01 08:00:09',
        '2025-10-12 06:13:19'
    ),
    (
        23,
        1,
        'Water',
        '2025-09-01',
        '2025-09-30',
        150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-13',
        NULL,
        'unpaid',
        '2025-10-01 11:15:21',
        '2025-10-01 11:15:21'
    ),
    (
        25,
        2,
        'Water',
        '2025-09-01',
        '2025-09-30',
        150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-13',
        NULL,
        'paid',
        '2025-10-01 11:15:22',
        '2025-10-13 00:17:53'
    ),
    (
        26,
        2,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-01 11:15:22',
        '2025-10-12 06:13:19'
    ),
    (
        28,
        16,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-01 11:15:23',
        '2025-10-01 11:15:23'
    ),
    (
        30,
        4,
        'Water',
        '2025-09-01',
        '2025-09-30',
        150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-13',
        NULL,
        'unpaid',
        '2025-10-01 11:15:23',
        '2025-10-02 11:03:22'
    ),
    (
        31,
        4,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        1500.00,
        0.00,
        NULL,
        1500.00,
        1550.00,
        50.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-01 11:15:23',
        '2025-10-13 11:54:08'
    ),
    (
        33,
        17,
        'Water',
        '2025-09-01',
        '2025-09-30',
        150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-13',
        NULL,
        'unpaid',
        '2025-10-01 11:15:23',
        '2025-10-01 11:15:23'
    ),
    (
        34,
        17,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-01 11:15:23',
        '2025-10-12 06:13:19'
    ),
    (
        36,
        5,
        'Water',
        '2025-09-01',
        '2025-09-30',
        150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-13',
        NULL,
        'unpaid',
        '2025-10-01 11:15:24',
        '2025-10-01 11:15:24'
    ),
    (
        37,
        5,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-01 11:15:24',
        '2025-10-01 11:15:24'
    ),
    (
        39,
        107,
        'Water',
        '2025-09-01',
        '2025-09-30',
        150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-13',
        NULL,
        'unpaid',
        '2025-10-01 11:15:24',
        '2025-10-01 11:15:24'
    ),
    (
        40,
        107,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-01 11:15:24',
        '2025-10-01 11:15:24'
    ),
    (
        52,
        3,
        'Water',
        '2025-09-01',
        '2025-09-30',
        150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-13',
        NULL,
        'paid',
        '2025-10-09 10:56:29',
        '2025-10-13 00:12:42'
    ),
    (
        53,
        3,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'paid',
        '2025-10-09 10:56:29',
        '2025-10-13 00:13:34'
    ),
    (
        55,
        6,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-11 02:45:45',
        '2025-10-11 02:45:45'
    ),
    (
        56,
        1,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-11 13:24:41',
        '2025-10-12 06:33:25'
    ),
    (
        57,
        2,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-11 13:24:41',
        '2025-10-12 06:33:25'
    ),
    (
        58,
        16,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        10260.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-11 13:24:41',
        '2025-10-12 14:54:30'
    ),
    (
        59,
        4,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-11 13:24:41',
        '2025-10-12 14:54:30'
    ),
    (
        60,
        17,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-11 13:24:42',
        '2025-10-12 06:33:25'
    ),
    (
        61,
        5,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-11 13:24:42',
        '2025-10-12 06:33:25'
    ),
    (
        62,
        107,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-11 13:24:42',
        '2025-10-12 06:33:25'
    ),
    (
        63,
        3,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        3780.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-11 13:24:42',
        '2025-10-12 06:33:25'
    ),
    (
        64,
        6,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        10584.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-11 13:24:42',
        '2025-10-12 14:54:30'
    ),
    (
        90,
        777,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        1740.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-13 02:00:55',
        '2025-10-13 02:00:55'
    ),
    (
        91,
        777,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-13 02:00:56',
        '2025-10-13 02:00:56'
    ),
    (
        92,
        493,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        3150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-15 14:03:36',
        '2025-10-15 14:03:36'
    ),
    (
        93,
        493,
        'Water',
        '2025-09-01',
        '2025-09-30',
        150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-13',
        NULL,
        'unpaid',
        '2025-10-15 14:03:36',
        '2025-10-15 14:03:36'
    ),
    (
        94,
        493,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-15 14:03:36',
        '2025-10-15 14:03:36'
    ),
    (
        95,
        494,
        'Rent',
        '2025-10-01',
        '2025-10-31',
        3150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-31',
        NULL,
        'unpaid',
        '2025-10-15 20:49:22',
        '2025-10-15 20:49:22'
    ),
    (
        96,
        494,
        'Water',
        '2025-09-01',
        '2025-09-30',
        150.00,
        0.00,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        '2025-10-13',
        NULL,
        'unpaid',
        '2025-10-15 20:49:22',
        '2025-10-15 20:49:22'
    ),
    (
        97,
        494,
        'Electricity',
        '2025-09-01',
        '2025-09-30',
        0.00,
        0.00,
        NULL,
        0.00,
        0.00,
        0.00,
        30.0000,
        '2025-10-13',
        '2025-10-19',
        'unpaid',
        '2025-10-15 20:49:22',
        '2025-10-15 20:49:22'
    );

CREATE TABLE IF NOT EXISTS billing_histories (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    billing_id bigint check (billing_id > 0) NOT NULL,
    field_changed varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    old_value varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    new_value varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    changed_by bigint check (changed_by > 0) NOT NULL,
    changed_at timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT billing_histories_billing_id_foreign FOREIGN KEY (billing_id) REFERENCES billing (id) ON DELETE CASCADE,
    CONSTRAINT billing_histories_changed_by_foreign FOREIGN KEY (changed_by) REFERENCES users (id)
);

CREATE INDEX billing_histories_billing_id_foreign ON billing_histories (billing_id);

CREATE INDEX billing_histories_changed_by_foreign ON billing_histories (changed_by);

CREATE TABLE IF NOT EXISTS billing_settings (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    utility_type varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    surcharge_rate decimal(5, 4) NOT NULL DEFAULT '0.0000',
    monthly_interest_rate decimal(5, 4) NOT NULL DEFAULT '0.0000',
    penalty_rate decimal(5, 4) NOT NULL DEFAULT '0.0000',
    discount_rate decimal(5, 4) NOT NULL DEFAULT '0.0000',
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT billing_settings_utility_type_unique UNIQUE (utility_type)
);

ALTER SEQUENCE billing_settings_seq RESTART WITH 4;

INSERT INTO
    billing_settings (
        id,
        utility_type,
        surcharge_rate,
        monthly_interest_rate,
        penalty_rate,
        discount_rate,
        created_at,
        updated_at
    )
VALUES (
        1,
        'Rent',
        0.2500,
        0.0200,
        0.0000,
        0.1000,
        '2025-09-12 19:28:46',
        '2025-10-06 23:13:17'
    ),
    (
        2,
        'Electricity',
        0.0000,
        0.0000,
        0.0000,
        0.0000,
        '2025-09-12 19:28:46',
        '2025-09-13 06:56:30'
    ),
    (
        3,
        'Water',
        0.0000,
        0.0000,
        0.0000,
        0.0000,
        '2025-09-12 19:28:46',
        '2025-09-13 06:56:33'
    );

CREATE TABLE IF NOT EXISTS billing_setting_histories (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    billing_setting_id bigint check (billing_setting_id > 0) NOT NULL,
    changed_by bigint check (changed_by > 0) NOT NULL,
    field_changed varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    old_value varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    new_value varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    changed_at timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT billing_setting_histories_billing_setting_id_foreign FOREIGN KEY (billing_setting_id) REFERENCES billing_settings (id) ON DELETE CASCADE,
    CONSTRAINT billing_setting_histories_changed_by_foreign FOREIGN KEY (changed_by) REFERENCES users (id)
);

ALTER SEQUENCE billing_setting_histories_seq RESTART WITH 6;

CREATE INDEX billing_setting_histories_billing_setting_id_foreign ON billing_setting_histories (billing_setting_id);

CREATE INDEX billing_setting_histories_changed_by_foreign ON billing_setting_histories (changed_by);

INSERT INTO
    billing_setting_histories (
        id,
        billing_setting_id,
        changed_by,
        field_changed,
        old_value,
        new_value,
        changed_at
    )
VALUES (
        3,
        1,
        1,
        'Discount Rate',
        '0',
        '10',
        '2025-10-06 22:53:00'
    ),
    (
        4,
        1,
        1,
        'Discount Rate',
        '10',
        '7',
        '2025-10-06 23:07:46'
    ),
    (
        5,
        1,
        1,
        'Discount Rate',
        '7',
        '10',
        '2025-10-06 23:13:17'
    );

CREATE TABLE IF NOT EXISTS cache (
    key varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    value mediumtext CHARACTER SET utf8mb4 NOT NULL,
    expiration int NOT NULL,
    PRIMARY KEY (key)
);

CREATE TABLE IF NOT EXISTS cache_locks (
    key varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    owner varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    expiration int NOT NULL,
    PRIMARY KEY (key)
);

CREATE TABLE IF NOT EXISTS failed_jobs (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    uuid varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    connection text CHARACTER SET utf8mb4 NOT NULL,
    queue text CHARACTER SET utf8mb4 NOT NULL,
    payload text CHARACTER SET utf8mb4 NOT NULL,
    exception text CHARACTER SET utf8mb4 NOT NULL,
    failed_at timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid)
);

CREATE TABLE IF NOT EXISTS jobs (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    queue varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    payload text CHARACTER SET utf8mb4 NOT NULL,
    attempts smallint check (attempts > 0) NOT NULL,
    reserved_at int check (reserved_at > 0) DEFAULT NULL,
    available_at int check (available_at > 0) NOT NULL,
    created_at int check (created_at > 0) NOT NULL,
    PRIMARY KEY (id)
);

ALTER SEQUENCE jobs_seq RESTART WITH 5;

CREATE INDEX jobs_queue_index ON jobs (queue);

CREATE TABLE IF NOT EXISTS job_batches (
    id varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    name varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    total_jobs int NOT NULL,
    pending_jobs int NOT NULL,
    failed_jobs int NOT NULL,
    failed_job_ids text CHARACTER SET utf8mb4 NOT NULL,
    options mediumtext CHARACTER SET utf8mb4,
    cancelled_at int DEFAULT NULL,
    created_at int NOT NULL,
    finished_at int DEFAULT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS migrations (
    id int check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    migration varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    batch int NOT NULL,
    PRIMARY KEY (id)
);

ALTER SEQUENCE migrations_seq RESTART WITH 29;

INSERT INTO
    migrations (id, migration, batch)
VALUES (
        1,
        '0001_01_01_000001_create_cache_table',
        1
    ),
    (
        2,
        '0001_01_01_000002_create_jobs_table',
        1
    ),
    (
        3,
        '2025_08_20_072813_create_roles_table',
        1
    ),
    (
        4,
        '2025_08_20_072958_create_users_table',
        1
    ),
    (
        5,
        '2025_08_20_073101_create_sections_table',
        1
    ),
    (
        6,
        '2025_08_20_073233_create_stalls_table',
        1
    ),
    (
        7,
        '2025_08_20_073722_create_rates_table',
        1
    ),
    (
        8,
        '2025_08_20_090139_create_rate_histories_table',
        1
    ),
    (
        9,
        '2025_08_20_090802_add_status_last_login_table',
        1
    ),
    (
        10,
        '2025_08_21_040321_create_audit_trails_table',
        1
    ),
    (
        11,
        '2025_08_21_040327_create_billing_table',
        1
    ),
    (
        12,
        '2025_08_21_040328_create_billing_histories_table',
        1
    ),
    (
        13,
        '2025_08_21_040333_create_payments_table',
        1
    ),
    (
        14,
        '2025_08_21_040339_create_utility_readings_table',
        1
    ),
    (
        15,
        '2025_08_21_040345_create_reading_edit_requests_table',
        1
    ),
    (
        16,
        '2025_08_21_040349_create_schedules_table',
        1
    ),
    (
        17,
        '2025_08_21_040358_create_schedule_histories_table',
        1
    ),
    (
        18,
        '2025_08_21_051402_create_sessions_table',
        1
    ),
    (
        19,
        '2025_08_23_021027_add_breakdown_details_to_billings_table',
        1
    ),
    (
        20,
        '2025_08_23_150005_add_rates_to_stalls_table',
        2
    ),
    (
        21,
        '2025_08_24_135735_create_personal_access_tokens_table',
        3
    ),
    (
        22,
        '2025_08_26_135822_add_unique_constraint_to_username_in_users_table',
        4
    ),
    (
        23,
        '2014_10_12_100000_create_password_resets_table',
        5
    ),
    (
        25,
        '2025_09_09_145748_add_consumption_to_utility_readings_table',
        6
    ),
    (
        26,
        '2025_09_13_031542_create_billing_settings_table',
        7
    ),
    (
        27,
        '2025_09_13_031548_create_billing_setting_histories_table',
        7
    ),
    (
        28,
        '2025_09_13_075558_modify_monthly_rate_in_stalls_table',
        8
    );

CREATE TABLE IF NOT EXISTS notifications (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    recipient_id bigint check (recipient_id > 0) NOT NULL,
    sender_id bigint check (sender_id > 0) DEFAULT NULL,
    channel varchar(30) check (channel in ('sms', 'in_app')) CHARACTER SET utf8mb4 NOT NULL,
    title varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    message text CHARACTER SET utf8mb4 NOT NULL,
    status varchar(30) check (
        status in ('sent', 'failed', 'pending')
    ) CHARACTER SET utf8mb4 DEFAULT 'pending',
    sent_at timestamp(0) NULL DEFAULT NULL,
    read_at timestamp(0) NULL DEFAULT NULL,
    created_at timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_notifications_recipient FOREIGN KEY (recipient_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_sender FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE SET NULL
);

ALTER SEQUENCE notifications_seq RESTART WITH 43;

CREATE INDEX fk_notifications_recipient ON notifications (recipient_id);

CREATE INDEX fk_notifications_sender ON notifications (sender_id);

INSERT INTO
    notifications (
        id,
        recipient_id,
        sender_id,
        channel,
        title,
        message,
        status,
        sent_at,
        read_at,
        created_at,
        updated_at
    )
VALUES (
        30,
        1,
        4,
        'in_app',
        'New Meter Readings Submitted',
        '{"text":"Meter Reader has submitted 1 new meter readings."}',
        'pending',
        NULL,
        '2025-10-05 21:53:59',
        '2025-10-05 15:08:40',
        '2025-10-05 15:08:40'
    ),
    (
        31,
        1,
        4,
        'in_app',
        'New Meter Reading Edit Request',
        '{"request_id":34,"reason":"wrong input","text":"New edit request from Meter Reader"}',
        'pending',
        NULL,
        '2025-10-05 21:53:59',
        '2025-10-05 15:33:00',
        '2025-10-05 15:33:00'
    ),
    (
        32,
        4,
        1,
        'in_app',
        'Edit Request approved',
        '{"text":"Your edit request for stall MS-04 has been approved.","request_id":34}',
        'pending',
        NULL,
        '2025-10-05 21:58:10',
        '2025-10-05 21:57:57',
        '2025-10-05 21:57:57'
    ),
    (
        33,
        1,
        2,
        'in_app',
        'Vendor Password Reset',
        '{"text":"Vendor Mr. Suave has requested a password reset.","vendor_id":2}',
        'pending',
        NULL,
        '2025-10-11 03:33:47',
        '2025-10-11 03:27:46',
        '2025-10-11 03:27:46'
    ),
    (
        34,
        1,
        2,
        'in_app',
        'Vendor Password Reset',
        '{"text":"Vendor Mr. Suave has requested a password reset.","vendor_id":2}',
        'pending',
        NULL,
        '2025-10-11 03:33:47',
        '2025-10-11 03:33:22',
        '2025-10-11 03:33:22'
    ),
    (
        35,
        1,
        2,
        'in_app',
        'Vendor Password Reset',
        '{"text":"Vendor Mr. Suave has requested a password reset.","vendor_id":2}',
        'pending',
        NULL,
        '2025-10-11 03:41:05',
        '2025-10-11 03:40:46',
        '2025-10-11 03:40:46'
    ),
    (
        36,
        1,
        2,
        'in_app',
        'Vendor Password Reset',
        '{"text":"Vendor Mr. Suave has requested a password reset.","vendor_id":2}',
        'pending',
        NULL,
        '2025-10-11 03:48:20',
        '2025-10-11 03:47:45',
        '2025-10-11 03:47:45'
    ),
    (
        37,
        1,
        2,
        'in_app',
        'Vendor Password Reset',
        '{"text":"Vendor Mr. Suave has requested a password reset.","vendor_id":2}',
        'pending',
        NULL,
        '2025-10-11 03:58:35',
        '2025-10-11 03:50:00',
        '2025-10-11 03:50:00'
    ),
    (
        38,
        1,
        2,
        'in_app',
        'Vendor Password Reset',
        '{"text":"Vendor Mr. Suave has requested a password reset.","vendor_id":2}',
        'pending',
        NULL,
        '2025-10-11 03:58:35',
        '2025-10-11 03:57:16',
        '2025-10-11 03:57:16'
    ),
    (
        39,
        1,
        4,
        'in_app',
        'New Meter Readings Submitted',
        '{"text":"Meter Reader has submitted 1 new meter readings."}',
        'pending',
        NULL,
        '2025-10-13 12:33:36',
        '2025-10-13 11:54:08',
        '2025-10-13 11:54:08'
    ),
    (
        40,
        1,
        105,
        'in_app',
        'Vendor Password Reset',
        '{"text":"Vendor Kyle has requested a password reset.","vendor_id":105}',
        'pending',
        NULL,
        '2025-10-13 12:33:36',
        '2025-10-13 11:59:48',
        '2025-10-13 11:59:48'
    ),
    (
        41,
        1,
        105,
        'in_app',
        'Vendor Password Reset',
        '{"text":"Vendor Kyle has requested a password reset.","vendor_id":105}',
        'pending',
        NULL,
        '2025-10-13 12:33:36',
        '2025-10-13 12:10:38',
        '2025-10-13 12:10:38'
    ),
    (
        42,
        1,
        105,
        'in_app',
        'Vendor Password Reset',
        '{"text":"Vendor Kyle has requested a password reset.","vendor_id":105}',
        'pending',
        NULL,
        '2025-10-13 12:33:36',
        '2025-10-13 12:21:02',
        '2025-10-13 12:21:02'
    );

CREATE TABLE IF NOT EXISTS password_resets (
    email varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    token varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    created_at timestamp(0) NULL DEFAULT NULL
);

CREATE INDEX password_resets_email_index ON password_resets (email);

CREATE TABLE IF NOT EXISTS payments (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    billing_id bigint check (billing_id > 0) NOT NULL,
    amount_paid decimal(10, 2) NOT NULL,
    payment_date date NOT NULL,
    penalty decimal(8, 2) NOT NULL DEFAULT '0.00',
    discount decimal(8, 2) NOT NULL DEFAULT '0.00',
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT payments_billing_id_foreign FOREIGN KEY (billing_id) REFERENCES billing (id)
);

ALTER SEQUENCE payments_seq RESTART WITH 17;

CREATE INDEX payments_billing_id_foreign ON payments (billing_id);

INSERT INTO
    payments (
        id,
        billing_id,
        amount_paid,
        payment_date,
        penalty,
        discount,
        created_at,
        updated_at
    )
VALUES (
        1,
        9,
        3780.00,
        '2025-09-26',
        945.00,
        0.00,
        '2025-09-25 21:24:08',
        '2025-10-31 23:58:05'
    ),
    (
        8,
        4,
        4725.00,
        '2025-10-06',
        0.00,
        0.00,
        '2025-10-06 11:02:21',
        '2025-10-31 23:54:14'
    ),
    (
        9,
        4,
        4725.00,
        '2025-10-06',
        0.00,
        0.00,
        '2025-10-06 11:44:36',
        '2025-10-31 23:54:14'
    ),
    (
        10,
        59,
        3402.00,
        '2025-10-12',
        0.00,
        0.00,
        '2025-10-12 07:15:26',
        '2025-10-31 23:58:05'
    ),
    (
        12,
        2,
        155.00,
        '2025-10-13',
        0.00,
        0.00,
        '2025-10-12 23:51:00',
        '2025-10-12 23:51:00'
    ),
    (
        13,
        16,
        155.00,
        '2025-10-13',
        0.00,
        0.00,
        '2025-10-13 00:04:56',
        '2025-10-13 00:04:56'
    ),
    (
        14,
        52,
        150.00,
        '2025-10-13',
        0.00,
        0.00,
        '2025-10-13 00:12:42',
        '2025-10-13 00:12:42'
    ),
    (
        15,
        53,
        0.00,
        '2025-10-13',
        0.00,
        0.00,
        '2025-10-13 00:13:34',
        '2025-10-13 00:13:34'
    ),
    (
        16,
        25,
        150.00,
        '2025-10-13',
        0.00,
        0.00,
        '2025-10-13 00:17:53',
        '2025-10-13 00:17:53'
    );

CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    tokenable_type varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    tokenable_id bigint check (tokenable_id > 0) NOT NULL,
    name text CHARACTER SET utf8mb4 NOT NULL,
    token varchar(64) CHARACTER SET utf8mb4 NOT NULL,
    abilities text CHARACTER SET utf8mb4,
    last_used_at timestamp(0) NULL DEFAULT NULL,
    expires_at timestamp(0) NULL DEFAULT NULL,
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT personal_access_tokens_token_unique UNIQUE (token)
);

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON personal_access_tokens (tokenable_type, tokenable_id);

CREATE INDEX personal_access_tokens_expires_at_index ON personal_access_tokens (expires_at);

CREATE TABLE IF NOT EXISTS rates (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    utility_type varchar(30) check (
        utility_type in (
            'Rent',
            'Electricity',
            'Water'
        )
    ) CHARACTER SET utf8mb4 NOT NULL,
    section_id bigint check (section_id > 0) DEFAULT NULL,
    rate decimal(10, 2) NOT NULL,
    monthly_rate decimal(10, 2) NOT NULL DEFAULT '0.00',
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT rates_section_id_foreign FOREIGN KEY (section_id) REFERENCES sections (id) ON DELETE SET NULL
);

ALTER SEQUENCE rates_seq RESTART WITH 3;

CREATE INDEX rates_section_id_foreign ON rates (section_id);

INSERT INTO
    rates (
        id,
        utility_type,
        section_id,
        rate,
        monthly_rate,
        created_at,
        updated_at
    )
VALUES (
        1,
        'Electricity',
        NULL,
        25.00,
        1974.00,
        '2025-08-26 03:38:31',
        '2025-10-26 23:50:25'
    ),
    (
        2,
        'Water',
        NULL,
        5.00,
        150.00,
        '2025-08-26 03:38:31',
        '2025-09-29 15:48:55'
    );

CREATE TABLE IF NOT EXISTS rate_histories (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    rate_id bigint check (rate_id > 0) NOT NULL,
    old_rate decimal(10, 2) NOT NULL,
    new_rate decimal(10, 2) NOT NULL,
    changed_by bigint check (changed_by > 0) NOT NULL,
    changed_at timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT rate_histories_changed_by_foreign FOREIGN KEY (changed_by) REFERENCES users (id),
    CONSTRAINT rate_histories_rate_id_foreign FOREIGN KEY (rate_id) REFERENCES rates (id) ON DELETE CASCADE
);

ALTER SEQUENCE rate_histories_seq RESTART WITH 77;

CREATE INDEX rate_histories_rate_id_foreign ON rate_histories (rate_id);

CREATE INDEX rate_histories_changed_by_foreign ON rate_histories (changed_by);

INSERT INTO
    rate_histories (
        id,
        rate_id,
        old_rate,
        new_rate,
        changed_by,
        changed_at
    )
VALUES (
        1,
        1,
        12.00,
        13.00,
        1,
        '2025-08-25 21:28:33'
    ),
    (
        5,
        1,
        13.00,
        15.00,
        1,
        '2025-08-25 21:47:12'
    ),
    (
        6,
        1,
        15.00,
        11.00,
        1,
        '2025-08-26 08:10:02'
    ),
    (
        7,
        1,
        11.00,
        10.00,
        1,
        '2025-08-26 16:15:58'
    ),
    (
        8,
        1,
        10.00,
        12.00,
        1,
        '2025-08-31 02:38:02'
    ),
    (
        10,
        1,
        12.00,
        11.00,
        1,
        '2025-08-31 02:38:29'
    ),
    (
        11,
        1,
        11.00,
        12.00,
        1,
        '2025-08-31 02:40:29'
    ),
    (
        13,
        1,
        12.00,
        11.00,
        1,
        '2025-08-31 02:40:56'
    ),
    (
        15,
        1,
        11.00,
        15.00,
        1,
        '2025-08-31 02:43:30'
    ),
    (
        16,
        1,
        15.00,
        12.00,
        1,
        '2025-09-01 06:02:41'
    ),
    (
        17,
        1,
        12.00,
        13.00,
        1,
        '2025-09-01 06:03:03'
    ),
    (
        18,
        1,
        13.00,
        10.00,
        1,
        '2025-09-01 06:28:36'
    ),
    (
        19,
        1,
        10.00,
        12.00,
        1,
        '2025-09-01 06:34:38'
    ),
    (
        20,
        1,
        12.00,
        20.00,
        1,
        '2025-09-01 06:42:18'
    ),
    (
        21,
        1,
        20.00,
        15.00,
        1,
        '2025-09-01 06:42:41'
    ),
    (
        22,
        1,
        15.00,
        11.00,
        1,
        '2025-09-01 07:04:13'
    ),
    (
        23,
        1,
        11.00,
        15.00,
        1,
        '2025-09-03 00:18:24'
    ),
    (
        24,
        1,
        15.00,
        10.00,
        1,
        '2025-09-04 21:09:17'
    ),
    (
        25,
        1,
        10.00,
        11.00,
        1,
        '2025-09-05 00:55:52'
    ),
    (
        26,
        1,
        11.00,
        12.00,
        1,
        '2025-09-05 08:39:08'
    ),
    (
        27,
        1,
        12.00,
        11.00,
        1,
        '2025-09-07 00:41:08'
    ),
    (
        28,
        1,
        11.00,
        13.00,
        1,
        '2025-09-07 00:49:07'
    ),
    (
        29,
        1,
        13.00,
        11.00,
        1,
        '2025-09-09 03:30:01'
    ),
    (
        30,
        1,
        11.00,
        13.00,
        1,
        '2025-09-10 04:46:06'
    ),
    (
        31,
        2,
        5.00,
        9.00,
        1,
        '2025-09-14 20:07:25'
    ),
    (
        32,
        2,
        9.00,
        5.00,
        1,
        '2025-09-14 20:08:59'
    ),
    (
        33,
        2,
        5.00,
        10.00,
        1,
        '2025-09-14 20:18:01'
    ),
    (
        34,
        2,
        10.00,
        300.00,
        1,
        '2025-09-14 22:39:41'
    ),
    (
        35,
        1,
        13.00,
        15.00,
        1,
        '2025-09-14 23:11:30'
    ),
    (
        36,
        2,
        300.00,
        20.00,
        1,
        '2025-09-14 23:11:45'
    ),
    (
        37,
        1,
        15.00,
        30.00,
        1,
        '2025-09-14 23:16:49'
    ),
    (
        38,
        2,
        20.00,
        40.00,
        1,
        '2025-09-14 23:17:01'
    ),
    (
        39,
        2,
        40.00,
        5.00,
        1,
        '2025-09-21 15:35:53'
    ),
    (
        40,
        1,
        30.00,
        15.00,
        1,
        '2025-09-26 20:23:34'
    ),
    (
        41,
        1,
        15.00,
        30.00,
        1,
        '2025-09-26 20:23:50'
    ),
    (
        42,
        1,
        30.00,
        29.00,
        1,
        '2025-09-29 07:58:15'
    ),
    (
        43,
        1,
        29.00,
        30.00,
        1,
        '2025-09-29 08:04:32'
    ),
    (
        44,
        1,
        30.00,
        29.00,
        1,
        '2025-09-29 08:22:29'
    ),
    (
        45,
        1,
        29.00,
        30.00,
        1,
        '2025-09-29 08:22:45'
    ),
    (
        46,
        1,
        30.00,
        29.00,
        1,
        '2025-09-29 08:31:26'
    ),
    (
        47,
        1,
        29.00,
        30.00,
        1,
        '2025-09-29 08:31:42'
    ),
    (
        48,
        1,
        30.00,
        29.00,
        1,
        '2025-09-29 08:35:01'
    ),
    (
        49,
        1,
        31.00,
        32.00,
        1,
        '2025-09-29 08:44:24'
    ),
    (
        50,
        1,
        32.00,
        30.00,
        1,
        '2025-09-29 08:44:37'
    ),
    (
        51,
        1,
        30.00,
        35.00,
        1,
        '2025-09-29 08:44:47'
    ),
    (
        52,
        1,
        35.00,
        30.00,
        1,
        '2025-09-29 08:44:57'
    ),
    (
        53,
        1,
        29.00,
        31.00,
        1,
        '2025-09-29 15:50:52'
    ),
    (
        54,
        1,
        31.00,
        25.00,
        1,
        '2025-09-29 15:51:19'
    ),
    (
        55,
        1,
        25.00,
        30.00,
        1,
        '2025-09-29 15:53:29'
    ),
    (
        56,
        1,
        30.00,
        29.00,
        1,
        '2025-09-29 16:23:38'
    ),
    (
        57,
        1,
        29.00,
        30.00,
        1,
        '2025-09-29 16:23:51'
    ),
    (
        58,
        1,
        30.00,
        24.00,
        1,
        '2025-09-29 16:27:08'
    ),
    (
        59,
        1,
        24.00,
        30.00,
        1,
        '2025-09-29 16:27:13'
    ),
    (
        60,
        1,
        30.00,
        25.00,
        1,
        '2025-09-29 16:37:35'
    ),
    (
        61,
        1,
        25.00,
        30.00,
        1,
        '2025-09-29 16:38:01'
    ),
    (
        62,
        1,
        30.00,
        25.00,
        1,
        '2025-09-29 16:40:38'
    ),
    (
        63,
        1,
        25.00,
        30.00,
        1,
        '2025-09-29 16:40:45'
    ),
    (
        64,
        1,
        30.00,
        32.00,
        1,
        '2025-09-30 14:45:26'
    ),
    (
        65,
        1,
        32.00,
        30.00,
        1,
        '2025-09-30 14:45:38'
    ),
    (
        66,
        1,
        30.00,
        35.00,
        1,
        '2025-09-30 15:19:43'
    ),
    (
        67,
        1,
        35.00,
        30.00,
        1,
        '2025-09-30 15:19:54'
    ),
    (
        68,
        1,
        30.00,
        32.00,
        1,
        '2025-09-30 15:21:04'
    ),
    (
        69,
        1,
        32.00,
        30.00,
        1,
        '2025-09-30 15:21:17'
    ),
    (
        70,
        1,
        30.00,
        35.00,
        1,
        '2025-09-30 15:21:28'
    ),
    (
        71,
        1,
        35.00,
        30.00,
        1,
        '2025-09-30 15:25:30'
    ),
    (
        72,
        1,
        30.00,
        15.00,
        1,
        '2025-10-09 09:07:38'
    ),
    (
        73,
        1,
        15.00,
        30.00,
        1,
        '2025-10-09 09:07:42'
    ),
    (
        74,
        1,
        30.00,
        25.00,
        1,
        '2025-10-26 23:49:24'
    ),
    (
        75,
        1,
        25.00,
        30.00,
        1,
        '2025-10-26 23:49:46'
    ),
    (
        76,
        1,
        30.00,
        25.00,
        1,
        '2025-10-26 23:50:25'
    );

CREATE TABLE IF NOT EXISTS reading_edit_requests (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    reading_id bigint check (reading_id > 0) NOT NULL,
    requested_by bigint check (requested_by > 0) NOT NULL,
    approved_by bigint check (approved_by > 0) DEFAULT NULL,
    reason text CHARACTER SET utf8mb4 NOT NULL,
    status varchar(30) check (
        status in (
            'pending',
            'approved',
            'rejected'
        )
    ) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'pending',
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT reading_edit_requests_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES users (id),
    CONSTRAINT reading_edit_requests_reading_id_foreign FOREIGN KEY (reading_id) REFERENCES utility_readings (id) ON DELETE CASCADE,
    CONSTRAINT reading_edit_requests_requested_by_foreign FOREIGN KEY (requested_by) REFERENCES users (id)
);

ALTER SEQUENCE reading_edit_requests_seq RESTART WITH 35;

CREATE INDEX reading_edit_requests_reading_id_foreign ON reading_edit_requests (reading_id);

CREATE INDEX reading_edit_requests_requested_by_foreign ON reading_edit_requests (requested_by);

CREATE INDEX reading_edit_requests_approved_by_foreign ON reading_edit_requests (approved_by);

CREATE TABLE IF NOT EXISTS reports (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    title varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    description text CHARACTER SET utf8mb4,
    report_type varchar(50) CHARACTER SET utf8mb4 NOT NULL,
    generated_by bigint check (generated_by > 0) NOT NULL,
    file_path varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_reports_generated_by FOREIGN KEY (generated_by) REFERENCES users (id) ON DELETE CASCADE
);

CREATE INDEX fk_reports_generated_by ON reports (generated_by);

CREATE TABLE IF NOT EXISTS roles (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    name varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT roles_name_unique UNIQUE (name)
);

ALTER SEQUENCE roles_seq RESTART WITH 5;

INSERT INTO
    roles (
        id,
        name,
        created_at,
        updated_at
    )
VALUES (
        1,
        'Admin',
        '2025-08-22 18:32:27',
        '2025-08-22 18:32:27'
    ),
    (
        2,
        'Vendor',
        '2025-08-22 18:32:27',
        '2025-08-22 18:32:27'
    ),
    (
        3,
        'Staff',
        '2025-08-22 18:32:27',
        '2025-08-22 18:32:27'
    ),
    (
        4,
        'Meter Reader Clerk',
        '2025-08-22 18:32:27',
        '2025-08-22 18:32:27'
    );

CREATE TABLE IF NOT EXISTS schedules (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    schedule_type varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    description varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
    schedule_day int DEFAULT NULL,
    schedule_date date NOT NULL,
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id)
);

ALTER SEQUENCE schedules_seq RESTART WITH 12;

INSERT INTO
    schedules (
        id,
        schedule_type,
        description,
        schedule_day,
        schedule_date,
        created_at,
        updated_at
    )
VALUES (
        1,
        'Meter Reading',
        '30',
        NULL,
        '2025-01-01',
        '2025-08-27 13:35:41',
        '2025-09-30 15:27:21'
    ),
    (
        3,
        'Due Date',
        '27',
        NULL,
        '2025-01-01',
        '2025-08-27 15:08:40',
        '2025-09-10 04:49:07'
    ),
    (
        4,
        'Disconnection',
        '29',
        NULL,
        '2025-01-01',
        '2025-08-27 15:08:40',
        '2025-09-07 00:41:38'
    ),
    (
        5,
        'undefined',
        'Not Set',
        NULL,
        '2025-09-12',
        '2025-09-11 21:44:22',
        '2025-09-11 22:16:51'
    ),
    (
        6,
        'Due Date - Electricity',
        '13',
        NULL,
        '2025-09-12',
        '2025-09-11 22:20:55',
        '2025-10-16 00:47:00'
    ),
    (
        7,
        'Disconnection - Electricity',
        '19',
        NULL,
        '2025-09-12',
        '2025-09-11 22:20:57',
        '2025-10-16 00:47:00'
    ),
    (
        8,
        'Due Date - Water',
        '13',
        NULL,
        '2025-09-12',
        '2025-09-11 22:20:59',
        '2025-10-16 00:47:00'
    ),
    (
        9,
        'SMS - Billing Statements',
        '08:00',
        NULL,
        '2025-09-30',
        '2025-09-30 05:38:25',
        '2025-10-16 00:54:41'
    ),
    (
        10,
        'SMS - Payment Reminders',
        '08:00',
        NULL,
        '2025-09-30',
        '2025-09-30 05:38:26',
        '2025-10-10 01:56:47'
    ),
    (
        11,
        'SMS - Overdue Alerts',
        '08:00',
        NULL,
        '2025-09-30',
        '2025-09-30 05:38:26',
        '2025-10-07 12:42:05'
    );

CREATE TABLE IF NOT EXISTS schedule_histories (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    schedule_id bigint check (schedule_id > 0) NOT NULL,
    field_changed varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    old_value varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    new_value varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    changed_by bigint check (changed_by > 0) NOT NULL,
    changed_at timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT schedule_histories_changed_by_foreign FOREIGN KEY (changed_by) REFERENCES users (id),
    CONSTRAINT schedule_histories_schedule_id_foreign FOREIGN KEY (schedule_id) REFERENCES schedules (id) ON DELETE CASCADE
);

ALTER SEQUENCE schedule_histories_seq RESTART WITH 248;

CREATE INDEX schedule_histories_schedule_id_foreign ON schedule_histories (schedule_id);

CREATE INDEX schedule_histories_changed_by_foreign ON schedule_histories (changed_by);

INSERT INTO
    schedule_histories (
        id,
        schedule_id,
        field_changed,
        old_value,
        new_value,
        changed_by,
        changed_at
    )
VALUES (
        1,
        1,
        'schedule_day',
        '25',
        '28',
        1,
        '2025-08-27 05:38:21'
    ),
    (
        2,
        1,
        'schedule_day',
        '28',
        '30',
        1,
        '2025-08-27 05:39:28'
    ),
    (
        3,
        3,
        'schedule_day',
        '15',
        '29',
        1,
        '2025-08-27 07:29:07'
    ),
    (
        4,
        4,
        'schedule_day',
        '25',
        '30',
        1,
        '2025-08-27 07:29:07'
    ),
    (
        5,
        3,
        'schedule_day',
        '29',
        '24',
        1,
        '2025-08-27 07:33:02'
    ),
    (
        6,
        4,
        'schedule_day',
        '30',
        '28',
        1,
        '2025-08-27 07:33:02'
    ),
    (
        7,
        1,
        'schedule_day',
        '30',
        '27',
        1,
        '2025-08-31 01:35:06'
    ),
    (
        8,
        3,
        'schedule_day',
        '24',
        '29',
        1,
        '2025-08-31 01:35:33'
    ),
    (
        9,
        4,
        'schedule_day',
        '28',
        '30',
        1,
        '2025-08-31 01:35:33'
    ),
    (
        10,
        1,
        'schedule_day',
        '27',
        '20',
        1,
        '2025-09-01 06:04:14'
    ),
    (
        11,
        3,
        'schedule_day',
        '29',
        '25',
        1,
        '2025-09-01 06:05:17'
    ),
    (
        12,
        4,
        'schedule_day',
        '30',
        '27',
        1,
        '2025-09-01 06:05:17'
    ),
    (
        13,
        3,
        'schedule_day',
        '25',
        '26',
        1,
        '2025-09-01 06:47:20'
    ),
    (
        14,
        1,
        'schedule_day',
        '20',
        '23',
        1,
        '2025-09-01 06:47:39'
    ),
    (
        15,
        1,
        'schedule_day',
        '23',
        '28',
        1,
        '2025-09-01 07:04:25'
    ),
    (
        16,
        3,
        'schedule_day',
        '26',
        '21',
        1,
        '2025-09-01 07:04:37'
    ),
    (
        17,
        4,
        'schedule_day',
        '27',
        '25',
        1,
        '2025-09-01 07:04:37'
    ),
    (
        18,
        1,
        'schedule_day',
        '28',
        '27',
        1,
        '2025-09-03 00:18:40'
    ),
    (
        19,
        4,
        'schedule_day',
        '25',
        '24',
        1,
        '2025-09-03 00:18:52'
    ),
    (
        20,
        3,
        'schedule_day',
        '21',
        '24',
        1,
        '2025-09-04 21:08:48'
    ),
    (
        21,
        4,
        'schedule_day',
        '24',
        '28',
        1,
        '2025-09-04 21:08:48'
    ),
    (
        22,
        1,
        'schedule_day',
        '27',
        '29',
        1,
        '2025-09-04 21:09:02'
    ),
    (
        23,
        1,
        'schedule_day',
        '29',
        '30',
        1,
        '2025-09-05 00:56:02'
    ),
    (
        24,
        3,
        'schedule_day',
        '24',
        '27',
        1,
        '2025-09-05 00:56:18'
    ),
    (
        25,
        4,
        'schedule_day',
        '28',
        '30',
        1,
        '2025-09-05 00:56:18'
    ),
    (
        26,
        3,
        'schedule_day',
        '27',
        '25',
        1,
        '2025-09-05 08:30:25'
    ),
    (
        27,
        1,
        'schedule_day',
        '30',
        '27',
        1,
        '2025-09-05 08:39:23'
    ),
    (
        28,
        4,
        'schedule_day',
        '30',
        '28',
        1,
        '2025-09-05 08:39:42'
    ),
    (
        29,
        1,
        'schedule_day',
        '27',
        '29',
        1,
        '2025-09-07 00:41:23'
    ),
    (
        30,
        3,
        'schedule_day',
        '25',
        '24',
        1,
        '2025-09-07 00:41:38'
    ),
    (
        31,
        4,
        'schedule_day',
        '28',
        '29',
        1,
        '2025-09-07 00:41:38'
    ),
    (
        32,
        3,
        'schedule_day',
        '24',
        '25',
        1,
        '2025-09-07 00:48:44'
    ),
    (
        33,
        1,
        'schedule_day',
        '29',
        '26',
        1,
        '2025-09-07 00:48:56'
    ),
    (
        34,
        1,
        'schedule_day',
        '26',
        '29',
        1,
        '2025-09-08 03:02:37'
    ),
    (
        35,
        1,
        'schedule_day',
        '29',
        '10',
        1,
        '2025-09-08 16:07:32'
    ),
    (
        36,
        1,
        'schedule_day',
        '10',
        '15',
        1,
        '2025-09-10 04:48:09'
    ),
    (
        37,
        3,
        'schedule_day',
        '25',
        '27',
        1,
        '2025-09-10 04:49:07'
    ),
    (
        38,
        5,
        'undefined',
        'Not Set',
        '13',
        1,
        '2025-09-11 21:44:23'
    ),
    (
        39,
        5,
        'undefined',
        '13',
        '19',
        1,
        '2025-09-11 21:44:23'
    ),
    (
        40,
        5,
        'undefined',
        '19',
        '13',
        1,
        '2025-09-11 21:44:23'
    ),
    (
        41,
        5,
        'undefined',
        '13',
        'Not Set',
        1,
        '2025-09-11 21:44:23'
    ),
    (
        42,
        5,
        'undefined',
        'Not Set',
        '13',
        1,
        '2025-09-11 21:44:52'
    ),
    (
        43,
        5,
        'undefined',
        '13',
        '19',
        1,
        '2025-09-11 21:44:52'
    ),
    (
        44,
        5,
        'undefined',
        '19',
        '13',
        1,
        '2025-09-11 21:44:52'
    ),
    (
        45,
        5,
        'undefined',
        '13',
        'Not Set',
        1,
        '2025-09-11 21:44:52'
    ),
    (
        46,
        5,
        'undefined',
        'Not Set',
        '13',
        1,
        '2025-09-11 22:02:47'
    ),
    (
        47,
        5,
        'undefined',
        '13',
        '19',
        1,
        '2025-09-11 22:02:47'
    ),
    (
        48,
        5,
        'undefined',
        '19',
        '13',
        1,
        '2025-09-11 22:02:47'
    ),
    (
        49,
        5,
        'undefined',
        '13',
        'Not Set',
        1,
        '2025-09-11 22:02:47'
    ),
    (
        50,
        5,
        'undefined',
        'Not Set',
        '13',
        1,
        '2025-09-11 22:03:24'
    ),
    (
        51,
        5,
        'undefined',
        '13',
        '19',
        1,
        '2025-09-11 22:03:25'
    ),
    (
        52,
        5,
        'undefined',
        '19',
        '13',
        1,
        '2025-09-11 22:03:25'
    ),
    (
        53,
        5,
        'undefined',
        '13',
        'Not Set',
        1,
        '2025-09-11 22:03:25'
    ),
    (
        54,
        5,
        'undefined',
        'Not Set',
        '13',
        1,
        '2025-09-11 22:04:10'
    ),
    (
        55,
        5,
        'undefined',
        '13',
        '19',
        1,
        '2025-09-11 22:04:11'
    ),
    (
        56,
        5,
        'undefined',
        '19',
        '13',
        1,
        '2025-09-11 22:04:11'
    ),
    (
        57,
        5,
        'undefined',
        '13',
        'Not Set',
        1,
        '2025-09-11 22:04:11'
    ),
    (
        58,
        5,
        'undefined',
        'Not Set',
        '13',
        1,
        '2025-09-12 06:08:15'
    ),
    (
        59,
        5,
        'undefined',
        '13',
        '19',
        1,
        '2025-09-12 06:08:16'
    ),
    (
        60,
        5,
        'undefined',
        '19',
        '13',
        1,
        '2025-09-12 06:08:16'
    ),
    (
        61,
        5,
        'undefined',
        '13',
        'Not Set',
        1,
        '2025-09-12 06:08:16'
    ),
    (
        62,
        5,
        'undefined',
        'Not Set',
        '13',
        1,
        '2025-09-12 06:15:57'
    ),
    (
        63,
        5,
        'undefined',
        '13',
        '19',
        1,
        '2025-09-12 06:15:57'
    ),
    (
        64,
        5,
        'undefined',
        '19',
        '13',
        1,
        '2025-09-12 06:15:57'
    ),
    (
        65,
        5,
        'undefined',
        '13',
        'Not Set',
        1,
        '2025-09-12 06:15:57'
    ),
    (
        66,
        5,
        'undefined',
        'Not Set',
        '13',
        1,
        '2025-09-12 06:16:51'
    ),
    (
        67,
        5,
        'undefined',
        '13',
        '19',
        1,
        '2025-09-12 06:16:51'
    ),
    (
        68,
        5,
        'undefined',
        '19',
        '13',
        1,
        '2025-09-12 06:16:51'
    ),
    (
        69,
        5,
        'undefined',
        '13',
        'Not Set',
        1,
        '2025-09-12 06:16:51'
    ),
    (
        70,
        6,
        'Due Date - Electricity',
        'Not Set',
        '13',
        1,
        '2025-09-12 06:20:57'
    ),
    (
        71,
        7,
        'Disconnection - Electricity',
        'Not Set',
        '19',
        1,
        '2025-09-12 06:20:57'
    ),
    (
        72,
        8,
        'Due Date - Water',
        'Not Set',
        '13',
        1,
        '2025-09-12 06:20:59'
    ),
    (
        73,
        1,
        'schedule_day',
        '15',
        '16',
        1,
        '2025-09-14 03:34:43'
    ),
    (
        74,
        1,
        'schedule_day',
        '16',
        '15',
        1,
        '2025-09-14 03:34:52'
    ),
    (
        75,
        1,
        'schedule_day',
        '15',
        '30',
        1,
        '2025-09-24 22:34:56'
    ),
    (
        76,
        1,
        'schedule_day',
        '30',
        '25',
        1,
        '2025-09-26 13:15:08'
    ),
    (
        77,
        1,
        'schedule_day',
        '25',
        '30',
        1,
        '2025-09-26 23:55:09'
    ),
    (
        78,
        1,
        'schedule_day',
        '30',
        '29',
        1,
        '2025-09-27 04:30:16'
    ),
    (
        79,
        1,
        'schedule_day',
        '29',
        '30',
        1,
        '2025-09-27 04:30:25'
    ),
    (
        80,
        6,
        'Due Date - Electricity',
        '13',
        '15',
        1,
        '2025-09-27 14:00:53'
    ),
    (
        81,
        8,
        'Due Date - Water',
        '13',
        '15',
        1,
        '2025-09-27 14:00:53'
    ),
    (
        82,
        6,
        'Due Date - Electricity',
        '15',
        '13',
        1,
        '2025-09-27 14:02:34'
    ),
    (
        83,
        8,
        'Due Date - Water',
        '15',
        '13',
        1,
        '2025-09-27 14:02:34'
    ),
    (
        84,
        6,
        'Due Date - Electricity',
        '13',
        '15',
        1,
        '2025-09-27 14:10:04'
    ),
    (
        85,
        7,
        'Disconnection - Electricity',
        '19',
        '20',
        1,
        '2025-09-27 14:10:04'
    ),
    (
        86,
        8,
        'Due Date - Water',
        '13',
        '15',
        1,
        '2025-09-27 14:10:04'
    ),
    (
        87,
        6,
        'Due Date - Electricity',
        '15',
        '13',
        1,
        '2025-09-27 14:15:37'
    ),
    (
        88,
        7,
        'Disconnection - Electricity',
        '20',
        '19',
        1,
        '2025-09-27 14:15:37'
    ),
    (
        89,
        8,
        'Due Date - Water',
        '15',
        '13',
        1,
        '2025-09-27 14:15:37'
    ),
    (
        90,
        1,
        'schedule_day',
        '30',
        '29',
        1,
        '2025-09-29 14:18:26'
    ),
    (
        91,
        1,
        'schedule_day',
        '29',
        '30',
        1,
        '2025-09-29 14:18:33'
    ),
    (
        92,
        6,
        'Due Date - Electricity',
        '13',
        '14',
        1,
        '2025-09-29 14:23:53'
    ),
    (
        93,
        7,
        'Disconnection - Electricity',
        '19',
        '20',
        1,
        '2025-09-29 14:23:53'
    ),
    (
        94,
        8,
        'Due Date - Water',
        '13',
        '14',
        1,
        '2025-09-29 14:23:53'
    ),
    (
        95,
        6,
        'Due Date - Electricity',
        '14',
        '13',
        1,
        '2025-09-29 14:24:12'
    ),
    (
        96,
        7,
        'Disconnection - Electricity',
        '20',
        '19',
        1,
        '2025-09-29 14:24:12'
    ),
    (
        97,
        8,
        'Due Date - Water',
        '14',
        '13',
        1,
        '2025-09-29 14:24:12'
    ),
    (
        171,
        9,
        'SMS - Billing Statements',
        '09:00',
        '07:00',
        1,
        '2025-09-30 15:18:32'
    ),
    (
        172,
        10,
        'SMS - Payment Reminders',
        '09:00',
        '07:00',
        1,
        '2025-09-30 15:18:33'
    ),
    (
        173,
        11,
        'SMS - Overdue Alerts',
        '09:00',
        '07:00',
        1,
        '2025-09-30 15:18:33'
    ),
    (
        174,
        9,
        'SMS - Billing Statements',
        '07:00',
        '09:00',
        1,
        '2025-09-30 15:26:16'
    ),
    (
        175,
        10,
        'SMS - Payment Reminders',
        '07:00',
        '09:00',
        1,
        '2025-09-30 15:26:16'
    ),
    (
        176,
        11,
        'SMS - Overdue Alerts',
        '07:00',
        '09:00',
        1,
        '2025-09-30 15:26:16'
    ),
    (
        177,
        9,
        'SMS - Billing Statements',
        '09:00',
        '21:00',
        1,
        '2025-09-30 15:26:31'
    ),
    (
        178,
        10,
        'SMS - Payment Reminders',
        '09:00',
        '21:00',
        1,
        '2025-09-30 15:26:31'
    ),
    (
        179,
        11,
        'SMS - Overdue Alerts',
        '09:00',
        '21:00',
        1,
        '2025-09-30 15:26:31'
    ),
    (
        180,
        1,
        'schedule_day',
        '30',
        '25',
        1,
        '2025-09-30 15:27:14'
    ),
    (
        181,
        1,
        'schedule_day',
        '25',
        '30',
        1,
        '2025-09-30 15:27:21'
    ),
    (
        182,
        6,
        'Due Date - Electricity',
        '13',
        '15',
        1,
        '2025-09-30 15:27:36'
    ),
    (
        183,
        7,
        'Disconnection - Electricity',
        '19',
        '20',
        1,
        '2025-09-30 15:27:36'
    ),
    (
        184,
        8,
        'Due Date - Water',
        '13',
        '15',
        1,
        '2025-09-30 15:27:36'
    ),
    (
        185,
        6,
        'Due Date - Electricity',
        '15',
        '13',
        1,
        '2025-09-30 15:27:48'
    ),
    (
        186,
        7,
        'Disconnection - Electricity',
        '20',
        '19',
        1,
        '2025-09-30 15:27:48'
    ),
    (
        187,
        8,
        'Due Date - Water',
        '15',
        '13',
        1,
        '2025-09-30 15:27:48'
    ),
    (
        188,
        9,
        'SMS - Billing Statements',
        '21:00',
        '22:00',
        1,
        '2025-09-30 15:48:03'
    ),
    (
        189,
        10,
        'SMS - Payment Reminders',
        '21:00',
        '22:00',
        1,
        '2025-09-30 15:48:03'
    ),
    (
        190,
        11,
        'SMS - Overdue Alerts',
        '21:00',
        '22:00',
        1,
        '2025-09-30 15:48:03'
    ),
    (
        191,
        9,
        'SMS - Billing Statements',
        '22:00',
        '19:00',
        1,
        '2025-09-30 15:58:49'
    ),
    (
        192,
        10,
        'SMS - Payment Reminders',
        '22:00',
        '19:00',
        1,
        '2025-09-30 15:58:49'
    ),
    (
        193,
        11,
        'SMS - Overdue Alerts',
        '22:00',
        '19:00',
        1,
        '2025-09-30 15:58:49'
    ),
    (
        194,
        9,
        'SMS - Billing Statements',
        '19:00',
        '08:00',
        1,
        '2025-10-04 12:55:01'
    ),
    (
        195,
        10,
        'SMS - Payment Reminders',
        '19:00',
        '08:00',
        1,
        '2025-10-04 12:55:01'
    ),
    (
        196,
        11,
        'SMS - Overdue Alerts',
        '19:00',
        '08:00',
        1,
        '2025-10-04 12:55:01'
    ),
    (
        197,
        9,
        'SMS - Billing Statements',
        '08:00',
        '07:00',
        1,
        '2025-10-04 22:50:17'
    ),
    (
        198,
        10,
        'SMS - Payment Reminders',
        '08:00',
        '07:00',
        1,
        '2025-10-04 22:50:17'
    ),
    (
        199,
        11,
        'SMS - Overdue Alerts',
        '08:00',
        '07:00',
        1,
        '2025-10-04 22:50:17'
    ),
    (
        200,
        11,
        'SMS - Overdue Alerts',
        '07:00',
        '07:10',
        1,
        '2025-10-04 23:05:30'
    ),
    (
        201,
        11,
        'SMS - Overdue Alerts',
        '07:10',
        '07:20',
        1,
        '2025-10-04 23:17:08'
    ),
    (
        202,
        11,
        'SMS - Overdue Alerts',
        '07:20',
        '08:20',
        1,
        '2025-10-05 00:14:38'
    ),
    (
        203,
        11,
        'SMS - Overdue Alerts',
        '08:20',
        '09:15',
        1,
        '2025-10-05 00:59:20'
    ),
    (
        204,
        11,
        'SMS - Overdue Alerts',
        '09:15',
        '09:30',
        1,
        '2025-10-05 01:20:28'
    ),
    (
        205,
        11,
        'SMS - Overdue Alerts',
        '09:30',
        '12:30',
        1,
        '2025-10-05 01:35:51'
    ),
    (
        206,
        11,
        'SMS - Overdue Alerts',
        '12:30',
        '11:30',
        1,
        '2025-10-05 03:03:21'
    ),
    (
        207,
        11,
        'SMS - Overdue Alerts',
        '11:30',
        '11:45',
        1,
        '2025-10-05 03:42:19'
    ),
    (
        208,
        9,
        'SMS - Billing Statements',
        '07:00',
        '08:00',
        1,
        '2025-10-05 04:19:25'
    ),
    (
        209,
        10,
        'SMS - Payment Reminders',
        '07:00',
        '08:00',
        1,
        '2025-10-05 04:19:25'
    ),
    (
        210,
        11,
        'SMS - Overdue Alerts',
        '11:45',
        '08:00',
        1,
        '2025-10-05 04:19:25'
    ),
    (
        211,
        9,
        'SMS - Billing Statements',
        '08:00',
        '10:00',
        1,
        '2025-10-05 10:53:54'
    ),
    (
        212,
        10,
        'SMS - Payment Reminders',
        '08:00',
        '10:00',
        1,
        '2025-10-05 10:53:54'
    ),
    (
        213,
        11,
        'SMS - Overdue Alerts',
        '08:00',
        '10:00',
        1,
        '2025-10-05 10:53:54'
    ),
    (
        214,
        9,
        'SMS - Billing Statements',
        '10:00',
        '08:00',
        1,
        '2025-10-05 10:54:15'
    ),
    (
        215,
        10,
        'SMS - Payment Reminders',
        '10:00',
        '08:00',
        1,
        '2025-10-05 10:54:15'
    ),
    (
        216,
        11,
        'SMS - Overdue Alerts',
        '10:00',
        '08:00',
        1,
        '2025-10-05 10:54:15'
    ),
    (
        217,
        11,
        'SMS - Overdue Alerts',
        '08:00',
        '19:05',
        1,
        '2025-10-05 11:02:30'
    ),
    (
        218,
        11,
        'SMS - Overdue Alerts',
        '19:05',
        '08:00',
        1,
        '2025-10-05 11:21:58'
    ),
    (
        219,
        10,
        'SMS - Payment Reminders',
        '08:00',
        '19:50',
        1,
        '2025-10-05 11:47:44'
    ),
    (
        220,
        10,
        'SMS - Payment Reminders',
        '19:50',
        '08:08',
        1,
        '2025-10-05 11:55:23'
    ),
    (
        221,
        10,
        'SMS - Payment Reminders',
        '08:08',
        '19:20',
        1,
        '2025-10-07 11:17:39'
    ),
    (
        222,
        11,
        'SMS - Overdue Alerts',
        '08:00',
        '19:20',
        1,
        '2025-10-07 11:17:39'
    ),
    (
        223,
        10,
        'SMS - Payment Reminders',
        '19:20',
        '19:22',
        1,
        '2025-10-07 11:21:34'
    ),
    (
        224,
        11,
        'SMS - Overdue Alerts',
        '19:20',
        '19:22',
        1,
        '2025-10-07 11:21:34'
    ),
    (
        225,
        10,
        'SMS - Payment Reminders',
        '19:22',
        '20:16',
        1,
        '2025-10-08 12:15:21'
    ),
    (
        226,
        11,
        'SMS - Overdue Alerts',
        '19:22',
        '20:16',
        1,
        '2025-10-08 12:15:21'
    ),
    (
        227,
        10,
        'SMS - Payment Reminders',
        '20:16',
        '20:18',
        1,
        '2025-10-08 12:16:54'
    ),
    (
        228,
        11,
        'SMS - Overdue Alerts',
        '20:16',
        '20:18',
        1,
        '2025-10-08 12:16:54'
    ),
    (
        229,
        10,
        'SMS - Payment Reminders',
        '20:18',
        '20:31',
        1,
        '2025-10-08 12:29:22'
    ),
    (
        230,
        11,
        'SMS - Overdue Alerts',
        '20:18',
        '20:31',
        1,
        '2025-10-08 12:29:22'
    ),
    (
        231,
        10,
        'SMS - Payment Reminders',
        '20:31',
        '08:00',
        1,
        '2025-10-07 12:42:05'
    ),
    (
        232,
        11,
        'SMS - Overdue Alerts',
        '20:31',
        '08:00',
        1,
        '2025-10-07 12:42:05'
    ),
    (
        233,
        10,
        'SMS - Payment Reminders',
        '08:00',
        '08:08',
        1,
        '2025-10-08 00:06:53'
    ),
    (
        234,
        10,
        'SMS - Payment Reminders',
        '08:08',
        '08:11',
        1,
        '2025-10-08 00:10:25'
    ),
    (
        235,
        10,
        'SMS - Payment Reminders',
        '08:11',
        '08:15',
        1,
        '2025-10-08 00:13:58'
    ),
    (
        236,
        10,
        'SMS - Payment Reminders',
        '08:15',
        '08:18',
        1,
        '2025-10-08 00:17:47'
    ),
    (
        237,
        10,
        'SMS - Payment Reminders',
        '08:18',
        '08:00',
        1,
        '2025-10-08 04:00:22'
    ),
    (
        238,
        10,
        'SMS - Payment Reminders',
        '08:00',
        '09:55',
        1,
        '2025-10-10 01:51:29'
    ),
    (
        239,
        10,
        'SMS - Payment Reminders',
        '09:55',
        '08:00',
        1,
        '2025-10-10 01:56:47'
    ),
    (
        240,
        6,
        'Due Date - Electricity',
        '13',
        '19',
        1,
        '2025-10-16 00:44:21'
    ),
    (
        241,
        7,
        'Disconnection - Electricity',
        '19',
        '21',
        1,
        '2025-10-16 00:44:21'
    ),
    (
        242,
        8,
        'Due Date - Water',
        '13',
        '19',
        1,
        '2025-10-16 00:44:21'
    ),
    (
        243,
        6,
        'Due Date - Electricity',
        '19',
        '13',
        1,
        '2025-10-16 00:47:00'
    ),
    (
        244,
        7,
        'Disconnection - Electricity',
        '21',
        '19',
        1,
        '2025-10-16 00:47:00'
    ),
    (
        245,
        8,
        'Due Date - Water',
        '19',
        '13',
        1,
        '2025-10-16 00:47:00'
    ),
    (
        246,
        9,
        'SMS - Billing Statements',
        '08:00',
        '09:00',
        1,
        '2025-10-16 00:54:33'
    ),
    (
        247,
        9,
        'SMS - Billing Statements',
        '09:00',
        '08:00',
        1,
        '2025-10-16 00:54:41'
    );

CREATE TABLE IF NOT EXISTS sections (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    name varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT name UNIQUE (name)
);

ALTER SEQUENCE sections_seq RESTART WITH 19;

INSERT INTO
    sections (
        id,
        name,
        created_at,
        updated_at
    )
VALUES (
        1,
        'Wet Section',
        '2025-08-22 18:32:27',
        '2025-08-22 18:32:27'
    ),
    (
        2,
        'Dry Section',
        '2025-08-22 18:32:28',
        '2025-08-22 18:32:28'
    ),
    (
        3,
        'Semi-Wet',
        '2025-08-22 18:32:28',
        '2025-08-22 18:32:28'
    );

CREATE TABLE IF NOT EXISTS sessions (
    id varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    user_id bigint check (user_id > 0) DEFAULT NULL,
    ip_address varchar(45) CHARACTER SET utf8mb4 DEFAULT NULL,
    user_agent text CHARACTER SET utf8mb4,
    payload text CHARACTER SET utf8mb4 NOT NULL,
    last_activity int NOT NULL,
    PRIMARY KEY (id)
);

CREATE INDEX sessions_user_id_index ON sessions (user_id);

CREATE INDEX sessions_last_activity_index ON sessions (last_activity);

INSERT INTO
    sessions (
        id,
        user_id,
        ip_address,
        user_agent,
        payload,
        last_activity
    )
VALUES (
        '4aviGGURCuAlNKFcco6a1eRrCB8b4LbZQXjYf98i',
        1,
        '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
        'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTXpiSVVYRUhUejF6S3VQUmJ1U1Z5MFdLVEJIaUVYNTZYRWhiMUpYSCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ub3RpZmljYXRpb25zL2ZldGNoIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJHdPSEZmTy9qZlQzbTBkNGVjcFMxQi5DZ3k4cUZhc2NlUEhMMUFOMWhxTzhzbmQ1cEtmeFZTIjt9',
        1761522760
    ),
    (
        'bagBGOY1mBz2Du40DOigvAGSX7wJYjW9xdhQKBO1',
        NULL,
        '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
        'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMnk2dHlsUzlqaElhZFJQT1JndDhncDlOMkFHVjVOekxFbUdGdDNqRSI7czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YToxOntzOjI2OiIwMUtCQk0yWVg3QzI5QjkySDNKRzVNMkdBQiI7Tjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',
        1764547984
    ),
    (
        'jBe9mL4YoiEHz3SVkgFZuHUAbcahEnL961YVo4qw',
        NULL,
        '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
        'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaUV5ZHFSbE14UTZobUxKSVo1a0NDS0N3YXgwcUd5Y0JOeWU5ekNNNiI7czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YToxOntzOjI2OiIwMUtCQk0zMEFTSks3RkdHVjRQUlJHVFZDTSI7Tjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',
        1764547985
    ),
    (
        'Lu6dYP4l77eIYuCBsbrArInvDgHZcxtbWLuE4zCi',
        6,
        '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
        'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiNVJQZ1FmTzZzc2tzUGxmZEx2UFBTbVJJc2dwTEhTWlFlWnJMTEk3RyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdGFmZi92ZW5kb3IvOTMvdmlldy1hcy12ZW5kb3ItcGFydGlhbCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjY7czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YTowOnt9fQ==',
        1761955875
    ),
    (
        'q8FtSLnVdzmUTlKDBpN7H8Fjqrl6Kd902pI5vBHK',
        NULL,
        '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
        'YTozOntzOjY6Il90b2tlbiI7czo0MDoiaUZzbnBEc3ZHNHFmZVY5dGoyMHFtR2g2NENXb1FVQm5Qc2NJb1VSZiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',
        1761955063
    ),
    (
        'uFY9hp5QfmEnmYC8VNidFIJh9Aav4CXgzauTuIAr',
        6,
        '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36',
        'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVnRUdllnNVhheHJlcmtQU1pJSkl1eXdwVDlzOGNOUE1GeFZ0Y1dMOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdGFmZiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjY7czoyMjoiUEhQREVCVUdCQVJfU1RBQ0tfREFUQSI7YTowOnt9fQ==',
        1761951912
    );

CREATE TABLE IF NOT EXISTS sms_notification_settings (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    name varchar(100) CHARACTER SET utf8mb4 NOT NULL,
    message_template text CHARACTER SET utf8mb4 NOT NULL,
    enabled smallint DEFAULT '1',
    created_at timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id)
);

ALTER SEQUENCE sms_notification_settings_seq RESTART WITH 5;

INSERT INTO
    sms_notification_settings (
        id,
        name,
        message_template,
        enabled,
        created_at,
        updated_at
    )
VALUES (
        1,
        'bill_statement_wet_section',
        'Bill Statement:nnMayad na aga, {{ vendor_name }}. Paisi tabi kan saimong bayadan: {{bill_details}}. nnAn kabuuan na babayadan: P{{total_due}}. Salamat!',
        1,
        '2025-08-28 09:33:07',
        '2025-10-16 00:54:19'
    ),
    (
        2,
        'bill_statement_dry_section',
        'Bill Statement:nnMayad na aga, {{ vendor_name }}. Paisi tabi kan saimong bayadan: {{bill_details}}. nnAn kabuuan na babayadan: P{{total_due}}. Salamat!',
        1,
        '2025-08-28 09:33:07',
        '2025-10-16 00:54:19'
    ),
    (
        3,
        'payment_reminder_template',
        'Mayad na aga, {{vendor_name}}. Reminder: Ini an saimong mga bayadan na dai pa nababayadan: nn{{ upcoming_bill_details }}nnSalamat!',
        1,
        '2025-08-28 09:33:07',
        '2025-10-16 00:54:19'
    ),
    (
        4,
        'overdue_alert_template',
        'Mayad na aga, {{ vendor_name }}. OVERDUE: An saimong bayadan para sa {{overdue_items}} lampas na sa due date. nnAn bagong total: P{{new_total_due}}. Salamat!',
        1,
        '2025-08-28 09:33:07',
        '2025-10-16 00:54:19'
    );

CREATE TABLE IF NOT EXISTS stalls (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    section_id bigint check (section_id > 0) NOT NULL,
    table_number varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
    vendor_id bigint check (vendor_id > 0) DEFAULT NULL,
    daily_rate decimal(10, 2) NOT NULL DEFAULT '0.00',
    monthly_rate decimal(10, 2) GENERATED ALWAYS AS (
        (
            case
                when (
                    (area is not null)
                    and (area > 0)
                ) then ((daily_rate * area) * 30)
                else (daily_rate * 30)
            end
        )
    ) STORED,
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    area decimal(10, 2) DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT vendor_id UNIQUE (vendor_id),
    CONSTRAINT vendor_id_2 UNIQUE (vendor_id),
    CONSTRAINT vendor_id_3 UNIQUE (vendor_id),
    CONSTRAINT stalls_section_id_foreign FOREIGN KEY (section_id) REFERENCES sections (id) ON DELETE CASCADE,
    CONSTRAINT stalls_vendor_id_foreign FOREIGN KEY (vendor_id) REFERENCES users (id) ON DELETE SET NULL
);

ALTER SEQUENCE stalls_seq RESTART WITH 785;

CREATE INDEX stalls_section_id_foreign ON stalls (section_id);

INSERT INTO
    stalls (
        id,
        section_id,
        table_number,
        vendor_id,
        daily_rate,
        created_at,
        updated_at,
        area
    )
VALUES (
        1,
        1,
        'MS-01',
        2,
        126.00,
        '2025-08-22 10:32:28',
        '2025-10-26 23:45:39',
        NULL
    ),
    (
        2,
        1,
        'MS-02',
        5,
        126.00,
        '2025-08-22 10:32:28',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        3,
        1,
        'MS-03',
        104,
        126.00,
        '2025-09-01 08:58:12',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        4,
        1,
        'MS-04',
        91,
        126.00,
        '2025-09-01 08:59:56',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        5,
        1,
        'MS-05',
        94,
        126.00,
        '2025-09-01 09:03:15',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        6,
        2,
        'L1',
        105,
        19.60,
        '2025-08-22 10:32:28',
        '2025-10-11 02:45:44',
        18.00
    ),
    (
        7,
        2,
        'L2',
        NULL,
        19.60,
        '2025-08-22 10:32:28',
        '2025-09-12 17:35:00',
        11.00
    ),
    (
        8,
        2,
        'L3',
        NULL,
        19.60,
        '2025-08-22 10:32:28',
        '2025-09-12 17:35:00',
        11.00
    ),
    (
        9,
        2,
        'L4',
        NULL,
        19.60,
        '2025-08-22 10:32:28',
        '2025-09-12 17:35:00',
        11.00
    ),
    (
        10,
        2,
        'L5',
        NULL,
        19.60,
        '2025-08-22 10:32:28',
        '2025-09-12 17:35:00',
        16.00
    ),
    (
        11,
        3,
        'FVS-01',
        NULL,
        105.00,
        '2025-08-22 10:32:28',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        12,
        3,
        'FVS-02',
        NULL,
        105.00,
        '2025-08-22 10:32:28',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        13,
        3,
        'FVS-03',
        NULL,
        105.00,
        '2025-08-22 10:32:28',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        14,
        3,
        'FVS-04',
        NULL,
        105.00,
        '2025-08-22 10:32:28',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        15,
        3,
        'FVS-05',
        NULL,
        105.00,
        '2025-08-24 06:59:41',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        16,
        2,
        'L6',
        89,
        19.00,
        '2025-08-31 21:01:10',
        '2025-09-12 17:35:00',
        18.00
    ),
    (
        17,
        1,
        'MS-06',
        93,
        126.00,
        '2025-09-01 09:23:20',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        107,
        1,
        'MS-07',
        102,
        126.00,
        '2025-09-10 13:24:06',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        489,
        1,
        'MS-08',
        NULL,
        126.00,
        '2025-09-13 00:59:45',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        490,
        1,
        'MS-09',
        NULL,
        126.00,
        '2025-09-13 00:59:45',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        491,
        1,
        'MS-10',
        NULL,
        126.00,
        '2025-09-13 00:59:45',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        492,
        1,
        'MS-11',
        NULL,
        126.00,
        '2025-09-13 00:59:45',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        493,
        1,
        'MS-12',
        117,
        105.00,
        '2025-09-13 00:59:45',
        '2025-10-15 14:03:36',
        NULL
    ),
    (
        494,
        1,
        'MS-13',
        118,
        105.00,
        '2025-09-13 00:59:45',
        '2025-10-15 20:49:22',
        NULL
    ),
    (
        495,
        1,
        'MS-14',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        496,
        1,
        'MS-15',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-10-10 02:36:01',
        NULL
    ),
    (
        497,
        1,
        'MS-16',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        498,
        1,
        'MS-17',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        499,
        1,
        'MS-18',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        500,
        1,
        'MS-19',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        501,
        1,
        'MS-20',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        502,
        1,
        'MS-21',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        503,
        1,
        'MS-22',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        504,
        1,
        'MS-23',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        505,
        1,
        'MS-24',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        506,
        1,
        'MS-25',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        507,
        1,
        'MS-26',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        508,
        1,
        'MS-27',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        509,
        1,
        'MS-28',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        510,
        1,
        'MS-29',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        511,
        1,
        'MS-30',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        512,
        1,
        'MS-31',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        513,
        1,
        'MS-32',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        514,
        1,
        'MS-33',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        515,
        1,
        'MS-34',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        516,
        1,
        'FS-35',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        517,
        1,
        'FS-36',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        518,
        1,
        'FS-37',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        519,
        1,
        'FS-38',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        520,
        1,
        'FS-39',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        521,
        1,
        'FS-40',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        522,
        1,
        'FS-41',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        523,
        1,
        'FS-42',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        524,
        1,
        'FS-43',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        525,
        1,
        'FS-44',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        526,
        1,
        'FS-45',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        527,
        1,
        'FS-46',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        528,
        1,
        'FS-47',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        529,
        1,
        'FS-48',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        530,
        1,
        'FS-49',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        531,
        1,
        'FS-50',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        532,
        1,
        'FS-51',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        533,
        1,
        'FS-52',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        534,
        1,
        'FS-53',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        535,
        1,
        'FS-54',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        536,
        1,
        'FS-55',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        537,
        1,
        'FS-56',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        538,
        1,
        'FS-57',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        539,
        1,
        'FS-58',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        540,
        1,
        'FS-59',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        541,
        1,
        'FS-60',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        542,
        1,
        'FS-61',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        543,
        1,
        'FS-62',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        544,
        1,
        'FS-63',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        545,
        1,
        'FS-64',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        546,
        1,
        'FS-65',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        547,
        1,
        'FS-66',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        548,
        1,
        'FS-67',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        549,
        1,
        'FS-68',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        550,
        1,
        'FS-69',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        551,
        1,
        'FS-70',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        552,
        1,
        'FS-71',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        553,
        1,
        'FS-72',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        554,
        1,
        'FS-73',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        555,
        1,
        'FS-74',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        556,
        1,
        'FS-75',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        557,
        1,
        'FS-76',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        558,
        1,
        'FS-77',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        559,
        1,
        'FS-78',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        560,
        1,
        'FS-79',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        561,
        1,
        'FS-80',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        562,
        1,
        'FS-81',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        563,
        1,
        'FS-82',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        564,
        1,
        'FS-83',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        565,
        1,
        'FS-84',
        NULL,
        84.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        566,
        1,
        'FS-85',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        567,
        1,
        'FS-86',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        568,
        1,
        'FS-87',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        569,
        1,
        'FS-88',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        570,
        1,
        'FS-89',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        571,
        1,
        'FS-90',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        572,
        1,
        'FS-91',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        573,
        1,
        'FS-92',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        574,
        1,
        'FS-93',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        575,
        1,
        'FS-94',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        576,
        1,
        'BS-1',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        577,
        1,
        'BS-2',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        578,
        1,
        'BS-3',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        579,
        1,
        'BS-4',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        580,
        1,
        'BS-5',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        581,
        1,
        'BS-6',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        582,
        1,
        'BS-7',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        583,
        1,
        'BS-8',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        584,
        1,
        'BS-9',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        585,
        1,
        'BS-10',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        586,
        1,
        'BS-11',
        NULL,
        47.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        587,
        1,
        'PFS-1',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        588,
        1,
        'PFS-2',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        589,
        1,
        'PFS-3',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        590,
        1,
        'PFS-4',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        591,
        1,
        'PFS-5',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        592,
        1,
        'PFS-6',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        593,
        1,
        'PFS-7',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        594,
        1,
        'PFS-8',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        595,
        1,
        'PFS-9',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        596,
        1,
        'PFS-10',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        597,
        1,
        'PFS-11',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        598,
        1,
        'PFS-12',
        NULL,
        105.00,
        '2025-09-13 00:59:45',
        '2025-09-13 00:59:45',
        NULL
    ),
    (
        616,
        2,
        'L7',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:00',
        5.00
    ),
    (
        617,
        2,
        'L8',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:00',
        5.70
    ),
    (
        618,
        2,
        'L9',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:00',
        10.00
    ),
    (
        619,
        2,
        'L10',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:00',
        10.50
    ),
    (
        620,
        2,
        'L11',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:00',
        10.00
    ),
    (
        621,
        2,
        'L12',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:00',
        10.00
    ),
    (
        622,
        2,
        'L13',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:00',
        10.00
    ),
    (
        623,
        2,
        'L14',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:00',
        10.00
    ),
    (
        624,
        2,
        'L15',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:00',
        10.00
    ),
    (
        625,
        2,
        'L16',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        626,
        2,
        'L17',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        627,
        2,
        'L18',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        628,
        2,
        'L19',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        629,
        2,
        'L20',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        630,
        2,
        'L21',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        631,
        2,
        'L22',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        632,
        2,
        'L23',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        633,
        2,
        'L24',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.50
    ),
    (
        634,
        2,
        'L25',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        635,
        2,
        'L26',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        636,
        2,
        'L27',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        637,
        2,
        'L28',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        638,
        2,
        'L29',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        639,
        2,
        'L30',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        640,
        2,
        'L31',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        641,
        2,
        'L32',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        642,
        2,
        'L33',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        643,
        2,
        'L34',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        644,
        2,
        'L35',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        645,
        2,
        'L36',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        646,
        2,
        'L37',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        647,
        2,
        'L38',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        648,
        2,
        'L39',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        649,
        2,
        'L40',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        650,
        2,
        'L41',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        651,
        2,
        'L42',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        652,
        2,
        'L43',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.50
    ),
    (
        653,
        2,
        'L44',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.50
    ),
    (
        654,
        2,
        'R1',
        NULL,
        19.60,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        18.00
    ),
    (
        655,
        2,
        'R2',
        NULL,
        19.60,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        11.50
    ),
    (
        656,
        2,
        'R3',
        NULL,
        19.60,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        11.00
    ),
    (
        657,
        2,
        'R4',
        NULL,
        19.60,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        11.00
    ),
    (
        658,
        2,
        'R5',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        5.70
    ),
    (
        659,
        2,
        'R6',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        5.00
    ),
    (
        660,
        2,
        'R7',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.70
    ),
    (
        661,
        2,
        'R8',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        662,
        2,
        'R9',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.50
    ),
    (
        663,
        2,
        'R10',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        664,
        2,
        'R11',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        665,
        2,
        'R12',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        666,
        2,
        'R13',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        667,
        2,
        'R14',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        668,
        2,
        'R15',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        669,
        2,
        'R16',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        670,
        2,
        'R17',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        671,
        2,
        'R18',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        672,
        2,
        'R19',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        673,
        2,
        'R20',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        674,
        2,
        'R21',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        675,
        2,
        'R22',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        676,
        2,
        'R23',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.50
    ),
    (
        677,
        2,
        'R24',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        678,
        2,
        'R25',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        679,
        2,
        'R26',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        680,
        2,
        'R27',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        681,
        2,
        'R28',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        682,
        2,
        'R29',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        683,
        2,
        'R30',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        684,
        2,
        'R31',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        685,
        2,
        'R32',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        686,
        2,
        'R33',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        687,
        2,
        'R34',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        688,
        2,
        'R35',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        10.00
    ),
    (
        689,
        2,
        'R36',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        690,
        2,
        'R37',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        691,
        2,
        'R38',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        692,
        2,
        'R39',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        693,
        2,
        'R40',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        694,
        2,
        'R41',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        8.70
    ),
    (
        695,
        2,
        'R42',
        NULL,
        15.40,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        7.30
    ),
    (
        696,
        2,
        'R43',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        7.20
    ),
    (
        697,
        2,
        'R44',
        NULL,
        17.50,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        7.20
    ),
    (
        698,
        3,
        'FVS-6',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        699,
        3,
        'FVS-7',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        700,
        3,
        'FVS-8',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        701,
        3,
        'FVS-9',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        702,
        3,
        'FVS-10',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        703,
        3,
        'FVS-11',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        704,
        3,
        'FVS-12',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        705,
        3,
        'FVS-13',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        706,
        3,
        'FVS-14',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        707,
        3,
        'FVS-15',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-12 17:35:50',
        NULL
    ),
    (
        708,
        3,
        'FVS-16',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        709,
        3,
        'FVS-17',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        710,
        3,
        'FVS-18',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        711,
        3,
        'FVS-19',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        712,
        3,
        'FVS-20',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        713,
        3,
        'FVS-21',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        714,
        3,
        'FVS-22',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        715,
        3,
        'FVS-23',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        716,
        3,
        'FVS-24',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        717,
        3,
        'FVS-25',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        718,
        3,
        'FVS-26',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        719,
        3,
        'FVS-27',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        720,
        3,
        'FVS-28',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        721,
        3,
        'FVS-29',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        722,
        3,
        'FVS-30',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        723,
        3,
        'FVS-31',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        724,
        3,
        'FVS-32',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        725,
        3,
        'FVS-33',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        726,
        3,
        'FVS-34',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        727,
        3,
        'FVS-35',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        728,
        3,
        'FVS-36',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        729,
        3,
        'FVS-37',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        730,
        3,
        'FVS-38',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        731,
        3,
        'FVS-39',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        732,
        3,
        'FVS-40',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        733,
        3,
        'FVS-41',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        734,
        3,
        'FVS-42',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        735,
        3,
        'FVS-43',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        736,
        3,
        'FVS-44',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        737,
        3,
        'FVS-45',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        738,
        3,
        'FVS-46',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        739,
        3,
        'FVS-47',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        740,
        3,
        'FVS-48',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        741,
        3,
        'FVS-49',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        742,
        3,
        'FVS-50',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        743,
        3,
        'FVS-51',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        744,
        3,
        'FVS-52',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        745,
        3,
        'FVS-53',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        746,
        3,
        'FVS-54',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        747,
        3,
        'FVS-55',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        748,
        3,
        'FVS-56',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        749,
        3,
        'FVS-57',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        750,
        3,
        'FVS-58',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        751,
        3,
        'FVS-59',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        752,
        3,
        'FVS-60',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        753,
        3,
        'FVS-61',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        754,
        3,
        'FVS-62',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        755,
        3,
        'FVS-63',
        NULL,
        84.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        756,
        3,
        'FVS-64',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        757,
        3,
        'FVS-65',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        758,
        3,
        'FVS-66',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        759,
        3,
        'FVS-67',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        760,
        3,
        'FVS-68',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        761,
        3,
        'FVS-69',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        762,
        3,
        'FVS-70',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        763,
        3,
        'FVS-71',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        764,
        3,
        'DFS-72',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        765,
        3,
        'DFS-73',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        766,
        3,
        'DFS-74',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        767,
        3,
        'DFS-75',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        768,
        3,
        'DFS-76',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        769,
        3,
        'DFS-77',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        770,
        3,
        'DFS-78',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        771,
        3,
        'DFS-79',
        NULL,
        105.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        772,
        3,
        'CPDFS-1',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        773,
        3,
        'CPDFS-2',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        774,
        3,
        'CPDFS-3',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        775,
        3,
        'CPDFS-4',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        776,
        3,
        'CPDFS-5',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        777,
        3,
        'CPDFS-6',
        106,
        58.00,
        '2025-09-13 01:32:30',
        '2025-10-13 02:00:54',
        NULL
    ),
    (
        778,
        3,
        'CPDFS-7',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        779,
        3,
        'CPDFS-8',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        780,
        3,
        'CPDFS-9',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        781,
        3,
        'CPDFS-10',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        782,
        3,
        'CPDFS-11',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    ),
    (
        783,
        3,
        'CPDFS-12',
        NULL,
        58.00,
        '2025-09-13 01:32:30',
        '2025-09-13 01:32:30',
        NULL
    );

CREATE TABLE IF NOT EXISTS users (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    role_id bigint check (role_id > 0) NOT NULL,
    name varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    username varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    password varchar(255) CHARACTER SET utf8mb4 NOT NULL,
    last_login timestamp(0) NULL DEFAULT NULL,
    status varchar(30) check (
        status in ('active', 'inactive')
    ) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'active',
    contact_number varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
    application_date date DEFAULT NULL,
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    password_changed_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT users_username_unique UNIQUE (username),
    CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
);

ALTER SEQUENCE users_seq RESTART WITH 138;

CREATE INDEX users_role_id_foreign ON users (role_id);

INSERT INTO
    users (
        id,
        role_id,
        name,
        username,
        password,
        last_login,
        status,
        contact_number,
        application_date,
        created_at,
        updated_at,
        password_changed_at
    )
VALUES (
        1,
        1,
        'Andy Po',
        'admin',
        '$2y$12$wOHFfO/jfT3m0d4ecpS1B.Cgy8qFascePHL1AN1hqO8snd5pKfxVS',
        '2025-10-26 23:45:11',
        'active',
        '09384432421',
        '2025-01-15',
        '2025-08-22 18:32:28',
        '2025-10-26 23:45:11',
        NULL
    ),
    (
        2,
        2,
        'Mr. Suave',
        'vendor',
        '$2y$12$txc.DLi6TRf5i.wlzBpqBuYpqqJJlNW7uU5bl8a9cgaReGByGCXNK',
        '2025-10-16 00:45:20',
        'active',
        '09384501002',
        '2025-08-24',
        '2025-08-24 03:08:23',
        '2025-10-16 00:45:20',
        '2025-10-11 04:01:03'
    ),
    (
        4,
        4,
        'Meter Reader',
        'meter_reader',
        '$2y$12$MpZxr9CsPpV3eT14DIYUZOe2LlOTCcwvHU6o7zVhDeBaB23rD3FLy',
        '2025-10-16 00:31:53',
        'active',
        '09506805354',
        '2025-01-15',
        '2025-08-29 03:01:46',
        '2025-10-16 00:31:53',
        NULL
    ),
    (
        5,
        2,
        'Splinter',
        'vendor2',
        '$2y$12$xeCh.t7OWP8t23Pw6QCdHOc/qeQorWwu/rMAbaxMvXy1eKpX.Efou',
        '2025-10-08 04:27:46',
        'active',
        '09450127891',
        '2025-04-05',
        '2025-08-29 00:47:17',
        '2025-10-08 04:27:46',
        NULL
    ),
    (
        6,
        3,
        'admin aide',
        'admin_aide',
        '$2y$12$HJNSpwPkD1MZvrLVydM9XeTP2g5XFOFA8DxOHeR4TU3z5FXbF8mCm',
        '2025-10-16 00:31:19',
        'active',
        NULL,
        NULL,
        '2025-08-31 16:45:11',
        '2025-10-16 00:31:19',
        NULL
    ),
    (
        89,
        2,
        'Lyra',
        'lyra',
        '$2y$12$PY/uPnri/ZnXCb4zktMtLek9U8qVpYCzA8NuAH02UsfPKymxxVNYa',
        '2025-10-04 14:31:40',
        'active',
        '09411231414',
        '2025-08-31',
        '2025-09-01 05:01:09',
        '2025-10-04 14:31:40',
        NULL
    ),
    (
        91,
        2,
        'Beyonce',
        'beyonce',
        '$2y$12$tQpxrLX1e.EjboMk9yNGHOtkPu6vgBEd0KaSBa1BX8X94ujn/IrNa',
        '2025-10-15 14:13:13',
        'active',
        '09701453721',
        '2025-08-27',
        '2025-09-01 16:59:56',
        '2025-10-15 14:13:13',
        '2025-10-11 14:46:41'
    ),
    (
        93,
        2,
        'Jean Antonette',
        'jean',
        '$2y$12$4WclFtrtwtrngTIJJd4tjeER49E69UCx.cASulCmTvh6EMl7AUiFi',
        '2025-10-13 00:21:37',
        'active',
        NULL,
        '2025-08-24',
        '2025-09-01 17:23:20',
        '2025-10-13 00:21:37',
        NULL
    ),
    (
        94,
        2,
        'Emmanuel',
        'emman',
        '$2y$12$qjQxAtkBlLhrIncQvVErwe7UCvfUqgR/WNBspTV24sbKtnT7OEEF2',
        '2025-10-03 04:54:21',
        'active',
        '09192731620',
        NULL,
        '2025-09-03 00:09:03',
        '2025-10-03 06:25:37',
        NULL
    ),
    (
        102,
        2,
        'Jeffer',
        'Jeff',
        '$2y$12$Cdxq9bqEyahINAJexfQOUug.jU7EWaV9SRyQ8.lt.cwFHI6gyDou.',
        '2025-10-06 21:57:58',
        'active',
        NULL,
        '2025-08-26',
        '2025-09-10 21:24:06',
        '2025-10-09 05:48:19',
        NULL
    ),
    (
        104,
        2,
        'Jonah',
        'jonah',
        '$2y$12$Ux0MSoXYZw5HVmTzwdlv0uzDcirTMVzVJBjrQtVIC5i6Tt9tprhB.',
        '2025-10-13 00:23:47',
        'active',
        NULL,
        '2025-09-01',
        '2025-10-09 09:30:08',
        '2025-10-13 00:23:47',
        '2025-10-11 01:43:15'
    ),
    (
        105,
        2,
        'Kyle',
        'kjoshua',
        '$2y$12$CUlBIuNztkWqXwDBuJ7HKui5oFDCRMN4L77DgsYovEz55RR7x7Gwe',
        '2025-10-15 14:14:04',
        'active',
        NULL,
        '2025-10-11',
        '2025-10-11 02:45:06',
        '2025-10-15 14:14:04',
        '2025-10-13 13:12:43'
    ),
    (
        106,
        2,
        'Vendor One',
        'Mariz',
        '$2y$12$wkn61nCYkAAhAs8KmnuZ6uGRv0FQJsaD.1BfS.xf1225da5srlgGa',
        '2025-10-13 02:01:56',
        'active',
        '09171234501',
        '2025-01-05',
        '2025-10-13 02:00:09',
        '2025-10-13 02:03:53',
        '2025-10-13 02:03:53'
    ),
    (
        107,
        2,
        'Vendor Two',
        'vendor_002',
        '$2y$12$hl1DTx.WGptgi7Yj0hymzufESqIFgowaxbkn5S8d.2t388F4oEXMG',
        NULL,
        'active',
        '09171234502',
        '2025-01-08',
        '2025-10-13 02:00:10',
        '2025-10-13 02:00:10',
        NULL
    ),
    (
        108,
        2,
        'Vendor Three',
        'vendor_003',
        '$2y$12$MVELXu7OzcpS6P2vEzIa1u1sceMJJG6cRkYGh9HT.sly1.h4VQKIC',
        NULL,
        'active',
        '09171234503',
        '2025-01-12',
        '2025-10-13 02:00:11',
        '2025-10-13 02:00:11',
        NULL
    ),
    (
        109,
        2,
        'Vendor Four',
        'vendor_004',
        '$2y$12$eKP8s5Y5yAdRdS1J4RE68uIsufQAUWBebpLV0KV8xAAumFa17BHjy',
        NULL,
        'active',
        '09171234504',
        '2025-01-15',
        '2025-10-13 02:00:11',
        '2025-10-13 02:00:11',
        NULL
    ),
    (
        110,
        2,
        'Vendor Five',
        'vendor_005',
        '$2y$12$SZummD7zFxKVbDbW5GV8PuiOH5Xg34YfL6Xq/Y/66Uy0Yns0EXNXe',
        NULL,
        'active',
        '09171234505',
        '2025-01-18',
        '2025-10-13 02:00:12',
        '2025-10-13 02:00:12',
        NULL
    ),
    (
        111,
        2,
        'Vendor Six',
        'vendor_006',
        '$2y$12$lr5JOGqFS19szYs0IzMEeu8EOTDUc3dhb7Rkfiwo8MtNcrCEtCEEO',
        NULL,
        'active',
        '09171234506',
        '2025-01-22',
        '2025-10-13 02:00:12',
        '2025-10-13 02:00:12',
        NULL
    ),
    (
        112,
        2,
        'Vendor Seven',
        'vendor_007',
        '$2y$12$7X4sdgOd3MbJhnKmY4zE0eRaAuE6H1RCX6J3o2lpaX1X26tEvcOtq',
        NULL,
        'active',
        '09171234507',
        '2025-01-25',
        '2025-10-13 02:00:13',
        '2025-10-13 02:00:13',
        NULL
    ),
    (
        113,
        2,
        'Vendor Eight',
        'vendor_008',
        '$2y$12$MLWO4rleRzUz/KYvL54uFucZyuyrJno4OC2pNUEaUxrQ9COjiKt42',
        NULL,
        'active',
        '09171234508',
        '2025-02-01',
        '2025-10-13 02:00:13',
        '2025-10-13 02:00:13',
        NULL
    ),
    (
        114,
        2,
        'Vendor Nine',
        'vendor_009',
        '$2y$12$lkT/dYq74h3bn24GayH3E.O7kfarKVuiGauxazQ/UagBHPOcsxpVi',
        NULL,
        'active',
        '09171234509',
        '2025-02-05',
        '2025-10-13 02:00:14',
        '2025-10-13 02:00:14',
        NULL
    ),
    (
        115,
        2,
        'Vendor Ten',
        'vendor_010',
        '$2y$12$t5FJKet7u.eMGDwsja7mQuQAWbhmqzMAzfQB7kkWM9QBEfsm/OBq2',
        NULL,
        'active',
        '09171234510',
        '2025-02-08',
        '2025-10-13 02:00:14',
        '2025-10-13 02:00:14',
        NULL
    ),
    (
        116,
        2,
        'Vendor Eleven',
        'vendor_011',
        '$2y$12$kJjTNzpspvM1dk1hYKv8jeaL4aiYofTZWu5ZJIthPlCPC4qMSdwT.',
        NULL,
        'active',
        '09171234511',
        '2025-02-12',
        '2025-10-13 02:00:15',
        '2025-10-13 02:00:15',
        NULL
    ),
    (
        117,
        2,
        'Vendor Twelve',
        'vendor012',
        '$2y$12$oz36bq8kbaVeknXtA7o6zOI13gFC7nVzgHjClBu.au.j0DolPf/NS',
        '2025-10-15 16:00:35',
        'active',
        '09171234512',
        '2025-02-15',
        '2025-10-13 02:00:15',
        '2025-10-15 16:00:35',
        '2025-10-15 15:50:24'
    ),
    (
        118,
        2,
        'Vendor Thirteen',
        'vendor013',
        '$2y$12$at1HMkALLtXw89mtt5vnf.ltnTY6y8znRkNd5Rp9tnUO9s/AaorBm',
        '2025-10-15 20:43:35',
        'active',
        '09171234513',
        '2025-02-18',
        '2025-10-13 02:00:16',
        '2025-10-15 20:44:50',
        '2025-10-15 20:44:50'
    ),
    (
        119,
        2,
        'Vendor Fourteen',
        'vendor_014',
        '$2y$12$GCPCqIf6/PHfxkyz.U/A2u9Xvvn0aiSGq94BJVCNRZIfCPxELp6pK',
        NULL,
        'active',
        '09171234514',
        '2025-02-22',
        '2025-10-13 02:00:16',
        '2025-10-13 02:00:16',
        NULL
    ),
    (
        120,
        2,
        'Vendor Fifteen',
        'vendor_015',
        '$2y$12$OW0sNqT6CiHuC4XifwZo7uEYZ1raOz6U5tAeYk4IDMR.0qBI9Lk4G',
        NULL,
        'active',
        '09171234515',
        '2025-02-25',
        '2025-10-13 02:00:17',
        '2025-10-13 02:00:17',
        NULL
    ),
    (
        121,
        2,
        'Vendor One',
        'vendor_001',
        '$2y$12$tXfF4w8A2zNr3fCDB1Vpje25ShNLXtsHuQN1lk5mc0Gwb1VaBpL4e',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:10:22',
        '2025-10-13 02:10:22',
        NULL
    ),
    (
        123,
        2,
        'Vendor Sixteen',
        'vendor_016',
        '$2y$12$8C7nMMblqg26.Gq1je5wUOnvKBGbn1Qp5SpGCvW2BelUDVP2541R6',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:50',
        '2025-10-13 02:12:50',
        NULL
    ),
    (
        124,
        2,
        'Vendor Seventeen',
        'vendor_017',
        '$2y$12$sR9H2pwj4M8GZUSCBBgz8OT57apCRhts2QHMeRA5kF4RH7JtlaUkG',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:51',
        '2025-10-13 02:12:51',
        NULL
    ),
    (
        125,
        2,
        'Vendor Eighteen',
        'vendor_018',
        '$2y$12$JjpmVL7rF9EUV31s4.pE2OpcDfqz5Ug2lsyH6OcH1Mp9MUFz6aE9a',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:51',
        '2025-10-13 02:12:51',
        NULL
    ),
    (
        126,
        2,
        'Vendor Nineteen',
        'vendor_019',
        '$2y$12$E77PP332CEYpbELwOxlm7.Nxl6EWHqOMTLnnvWoJGiRDyi7ikfcy.',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:51',
        '2025-10-13 02:12:51',
        NULL
    ),
    (
        127,
        2,
        'Vendor Twenty',
        'vendor_020',
        '$2y$12$BDCUHPtYA/3EiLn.UcycNelsUXUAWuZgL1QkxIVP1sR8JEMNDMasi',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:52',
        '2025-10-13 02:12:52',
        NULL
    ),
    (
        128,
        2,
        'Vendor Twenty One',
        'vendor_021',
        '$2y$12$AaIFmw4U6eHOQlzs3tuXGuwKoMiLqpjf9d1LFs1gUG5MFo7pKpk5e',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:52',
        '2025-10-13 02:12:52',
        NULL
    ),
    (
        129,
        2,
        'Vendor Twenty Two',
        'vendor_022',
        '$2y$12$jU1oREkD/gsj.RgXUGTbh.Bfpn6GmH07UrBTwu5QBVVadYwVOPl3S',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:53',
        '2025-10-13 02:12:53',
        NULL
    ),
    (
        130,
        2,
        'Vendor Twenty Three',
        'vendor_023',
        '$2y$12$poWTS99ZBO1rx31g9uFIUerwZctoGvMC/jE6HM1n1FrzoPz/X7svi',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:53',
        '2025-10-13 02:12:53',
        NULL
    ),
    (
        131,
        2,
        'Vendor Twenty Four',
        'vendor_024',
        '$2y$12$dGInupA/g.cnfFxJjFjeIetr7FUCiFjMa6d/c53yBGUhUE1AGZKOq',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:54',
        '2025-10-13 02:12:54',
        NULL
    ),
    (
        132,
        2,
        'Vendor Twenty Five',
        'vendor_025',
        '$2y$12$hqCOC7touYENVCFijdshpe9VgTPu.T6ZC2/KQ7WMXTu.PGW4noqNS',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:54',
        '2025-10-13 02:12:54',
        NULL
    ),
    (
        133,
        2,
        'Vendor Twenty Six',
        'vendor_026',
        '$2y$12$ZDgbsg73WXhih00z1iJ0LuNLjLJ7Qv/Z6hCCVqgU02kJyII8qmEKu',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:55',
        '2025-10-13 02:12:55',
        NULL
    ),
    (
        134,
        2,
        'Vendor Twenty Seven',
        'vendor_027',
        '$2y$12$EK5QtNH78RJKsYrkDuwjlOv3ZaqTMJKk4XXUMCNnA82UunssMzaJy',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:55',
        '2025-10-13 02:12:55',
        NULL
    ),
    (
        135,
        2,
        'Vendor Twenty Eight',
        'vendor_028',
        '$2y$12$BhXsUaCN/KYhqCO/L6cuz.xQSE/B/YZWxqufHCR4UX9y38tD6UQ3i',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:56',
        '2025-10-13 02:12:56',
        NULL
    ),
    (
        136,
        2,
        'Vendor Twenty Nine',
        'vendor_029',
        '$2y$12$xRTL0kU7Xwb4MH71q5CtDOPazmzhcE2wE6nVuVTJo899CYu3Rlhoq',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:56',
        '2025-10-13 02:12:56',
        NULL
    ),
    (
        137,
        2,
        'Vendor Thirty',
        'vendor_030',
        '$2y$12$ySr0.sUGRAjpzJss/6D7COzBUyUf9rECZ.dDxvMNwCszbZ7RyegVi',
        NULL,
        'active',
        NULL,
        NULL,
        '2025-10-13 02:12:57',
        '2025-10-13 02:12:57',
        NULL
    );

CREATE TABLE IF NOT EXISTS utility_readings (
    id bigint check (id > 0) NOT NULL GENERATED ALWAYS AS IDENTITY,
    stall_id bigint check (stall_id > 0) NOT NULL,
    utility_type varchar(30) check (
        utility_type in ('Electricity', 'Water')
    ) CHARACTER SET utf8mb4 NOT NULL,
    reading_date date NOT NULL,
    current_reading decimal(10, 2) NOT NULL,
    previous_reading decimal(10, 2) NOT NULL,
    created_at timestamp(0) NULL DEFAULT NULL,
    updated_at timestamp(0) NULL DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT utility_readings_stall_id_foreign FOREIGN KEY (stall_id) REFERENCES stalls (id)
);

ALTER SEQUENCE utility_readings_seq RESTART WITH 2699;

CREATE INDEX utility_readings_stall_id_foreign ON utility_readings (stall_id);

INSERT INTO
    utility_readings (
        id,
        stall_id,
        utility_type,
        reading_date,
        current_reading,
        previous_reading,
        created_at,
        updated_at
    )
VALUES (
        1514,
        1,
        'Electricity',
        '2025-08-31',
        1550.00,
        1500.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:39'
    ),
    (
        1515,
        2,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1516,
        3,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1517,
        4,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1518,
        5,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1519,
        17,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1520,
        107,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1521,
        489,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1522,
        490,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1523,
        491,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1524,
        492,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1525,
        493,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1526,
        494,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1527,
        495,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1528,
        496,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1529,
        497,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1530,
        498,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1531,
        499,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1532,
        500,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1533,
        501,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1534,
        502,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1535,
        503,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1536,
        504,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1537,
        505,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1538,
        506,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1539,
        507,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1540,
        508,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1541,
        509,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1542,
        510,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1543,
        511,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1544,
        512,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1545,
        513,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1546,
        514,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1547,
        515,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1548,
        516,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1549,
        517,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1550,
        518,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1551,
        519,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1552,
        520,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1553,
        521,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1554,
        522,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1555,
        523,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1556,
        524,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1557,
        525,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1558,
        526,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1559,
        527,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1560,
        528,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1561,
        529,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1562,
        530,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1563,
        531,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1564,
        532,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1565,
        533,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1566,
        534,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1567,
        535,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1568,
        536,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1569,
        537,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1570,
        538,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1571,
        539,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1572,
        540,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1573,
        541,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1574,
        542,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1575,
        543,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1576,
        544,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1577,
        545,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1578,
        546,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1579,
        547,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1580,
        548,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1581,
        549,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1582,
        550,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1583,
        551,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1584,
        552,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1585,
        553,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1586,
        554,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1587,
        555,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1588,
        556,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1589,
        557,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1590,
        558,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1591,
        559,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1592,
        560,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1593,
        561,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1594,
        562,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1595,
        563,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1596,
        564,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1597,
        565,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1598,
        566,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1599,
        567,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1600,
        568,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1601,
        569,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1602,
        570,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1603,
        571,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1604,
        572,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1605,
        573,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1606,
        574,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1607,
        575,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1608,
        576,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1609,
        577,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1610,
        578,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1611,
        579,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1612,
        580,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1613,
        581,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1614,
        582,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1615,
        583,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1616,
        584,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1617,
        585,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1618,
        586,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1619,
        587,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1620,
        588,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1621,
        589,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1622,
        590,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1623,
        591,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1624,
        592,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1625,
        593,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1626,
        594,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1627,
        595,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1628,
        596,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1629,
        597,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1630,
        598,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1631,
        6,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1632,
        7,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1633,
        8,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1634,
        9,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1635,
        10,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1636,
        16,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1637,
        616,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1638,
        617,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1639,
        618,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1640,
        619,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1641,
        620,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1642,
        621,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1643,
        622,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1644,
        623,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1645,
        624,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1646,
        625,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1647,
        626,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1648,
        627,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1649,
        628,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1650,
        629,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1651,
        630,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1652,
        631,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1653,
        632,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1654,
        633,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1655,
        634,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1656,
        635,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1657,
        636,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1658,
        637,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1659,
        638,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1660,
        639,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1661,
        640,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1662,
        641,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1663,
        642,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1664,
        643,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1665,
        644,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1666,
        645,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1667,
        646,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1668,
        647,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1669,
        648,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1670,
        649,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1671,
        650,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1672,
        651,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1673,
        652,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1674,
        653,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1675,
        654,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1676,
        655,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1677,
        656,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1678,
        657,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1679,
        658,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1680,
        659,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1681,
        660,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1682,
        661,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1683,
        662,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1684,
        663,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1685,
        664,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1686,
        665,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1687,
        666,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1688,
        667,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1689,
        668,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1690,
        669,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1691,
        670,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1692,
        671,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1693,
        672,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1694,
        673,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1695,
        674,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1696,
        675,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1697,
        676,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1698,
        677,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1699,
        678,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1700,
        679,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1701,
        680,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1702,
        681,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1703,
        682,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1704,
        683,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1705,
        684,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1706,
        685,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1707,
        686,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1708,
        687,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1709,
        688,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1710,
        689,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1711,
        690,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1712,
        691,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1713,
        692,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1714,
        693,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1715,
        694,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1716,
        695,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1717,
        696,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1718,
        697,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1719,
        11,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1720,
        12,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1721,
        13,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1722,
        14,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1723,
        15,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1724,
        698,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1725,
        699,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1726,
        700,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1727,
        701,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1728,
        702,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1729,
        703,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1730,
        704,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1731,
        705,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1732,
        706,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1733,
        707,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1734,
        708,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1735,
        709,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1736,
        710,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1737,
        711,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1738,
        712,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1739,
        713,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1740,
        714,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1741,
        715,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1742,
        716,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1743,
        717,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1744,
        718,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1745,
        719,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1746,
        720,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1747,
        721,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1748,
        722,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1749,
        723,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1750,
        724,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1751,
        725,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1752,
        726,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1753,
        727,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1754,
        728,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1755,
        729,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1756,
        730,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1757,
        731,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1758,
        732,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1759,
        733,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1760,
        734,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1761,
        735,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1762,
        736,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1763,
        737,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1764,
        738,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1765,
        739,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1766,
        740,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1767,
        741,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1768,
        742,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1769,
        743,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1770,
        744,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1771,
        745,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1772,
        746,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1773,
        747,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1774,
        748,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1775,
        749,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1776,
        750,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1777,
        751,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1778,
        752,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1779,
        753,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1780,
        754,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1781,
        755,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1782,
        756,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1783,
        757,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1784,
        758,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1785,
        759,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1786,
        760,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1787,
        761,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1788,
        762,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1789,
        763,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1790,
        764,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1791,
        765,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1792,
        766,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1793,
        767,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1794,
        768,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1795,
        769,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1796,
        770,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1797,
        771,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1798,
        772,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1799,
        773,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1800,
        774,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1801,
        775,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1802,
        776,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1803,
        777,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1804,
        778,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1805,
        779,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1806,
        780,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1807,
        781,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1808,
        782,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        1809,
        783,
        'Electricity',
        '2025-08-31',
        0.00,
        0.00,
        '2025-09-25 02:34:11',
        '2025-09-25 02:34:11'
    ),
    (
        2106,
        3,
        'Electricity',
        '2025-10-09',
        0.00,
        0.00,
        '2025-10-09 10:31:08',
        '2025-10-09 10:31:08'
    ),
    (
        2107,
        3,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-09 10:56:30',
        '2025-10-09 10:56:30'
    ),
    (
        2108,
        6,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-11 02:45:45',
        '2025-10-11 02:45:45'
    ),
    (
        2109,
        1,
        'Electricity',
        '2025-09-30',
        0.00,
        1550.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2110,
        2,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2111,
        3,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2112,
        4,
        'Electricity',
        '2025-09-30',
        1550.00,
        1500.00,
        '2025-10-12 05:56:00',
        '2025-10-13 11:54:08'
    ),
    (
        2113,
        5,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2114,
        17,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2115,
        107,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2116,
        489,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2117,
        490,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2118,
        491,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2119,
        492,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2120,
        493,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2121,
        494,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2122,
        495,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2123,
        496,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2124,
        497,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2125,
        498,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2126,
        499,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2127,
        500,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2128,
        501,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2129,
        502,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2130,
        503,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2131,
        504,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2132,
        505,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2133,
        506,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2134,
        507,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2135,
        508,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2136,
        509,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2137,
        510,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2138,
        511,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2139,
        512,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2140,
        513,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2141,
        514,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2142,
        515,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2143,
        516,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2144,
        517,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2145,
        518,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2146,
        519,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2147,
        520,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2148,
        521,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2149,
        522,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2150,
        523,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2151,
        524,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2152,
        525,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2153,
        526,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2154,
        527,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2155,
        528,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2156,
        529,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2157,
        530,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2158,
        531,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2159,
        532,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2160,
        533,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2161,
        534,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2162,
        535,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2163,
        536,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2164,
        537,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2165,
        538,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2166,
        539,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2167,
        540,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2168,
        541,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2169,
        542,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2170,
        543,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2171,
        544,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2172,
        545,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2173,
        546,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2174,
        547,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2175,
        548,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2176,
        549,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2177,
        550,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2178,
        551,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2179,
        552,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2180,
        553,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2181,
        554,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2182,
        555,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2183,
        556,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2184,
        557,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2185,
        558,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2186,
        559,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2187,
        560,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2188,
        561,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2189,
        562,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2190,
        563,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2191,
        564,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2192,
        565,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2193,
        566,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2194,
        567,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2195,
        568,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2196,
        569,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2197,
        570,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2198,
        571,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2199,
        572,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2200,
        573,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2201,
        574,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2202,
        575,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2203,
        576,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2204,
        577,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2205,
        578,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2206,
        579,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2207,
        580,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2208,
        581,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2209,
        582,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2210,
        583,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2211,
        584,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2212,
        585,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2213,
        586,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2214,
        587,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2215,
        588,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2216,
        589,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2217,
        590,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2218,
        591,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2219,
        592,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2220,
        593,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2221,
        594,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2222,
        595,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2223,
        596,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2224,
        597,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2225,
        598,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2226,
        6,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2227,
        7,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2228,
        8,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2229,
        9,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2230,
        10,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2231,
        16,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2232,
        616,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2233,
        617,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2234,
        618,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2235,
        619,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2236,
        620,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2237,
        621,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2238,
        622,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2239,
        623,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2240,
        624,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2241,
        625,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2242,
        626,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2243,
        627,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2244,
        628,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2245,
        629,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2246,
        630,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2247,
        631,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2248,
        632,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2249,
        633,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2250,
        634,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2251,
        635,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2252,
        636,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2253,
        637,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2254,
        638,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2255,
        639,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2256,
        640,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2257,
        641,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2258,
        642,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2259,
        643,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2260,
        644,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2261,
        645,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2262,
        646,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2263,
        647,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2264,
        648,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2265,
        649,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2266,
        650,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2267,
        651,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2268,
        652,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2269,
        653,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2270,
        654,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2271,
        655,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2272,
        656,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2273,
        657,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2274,
        658,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2275,
        659,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2276,
        660,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2277,
        661,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2278,
        662,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2279,
        663,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2280,
        664,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2281,
        665,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2282,
        666,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2283,
        667,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2284,
        668,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2285,
        669,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2286,
        670,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2287,
        671,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2288,
        672,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2289,
        673,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2290,
        674,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2291,
        675,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2292,
        676,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2293,
        677,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2294,
        678,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2295,
        679,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2296,
        680,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2297,
        681,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2298,
        682,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2299,
        683,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2300,
        684,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2301,
        685,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2302,
        686,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2303,
        687,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2304,
        688,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2305,
        689,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2306,
        690,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2307,
        691,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2308,
        692,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2309,
        693,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2310,
        694,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2311,
        695,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2312,
        696,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2313,
        697,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2314,
        11,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2315,
        12,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2316,
        13,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2317,
        14,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2318,
        15,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2319,
        698,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2320,
        699,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2321,
        700,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2322,
        701,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2323,
        702,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2324,
        703,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2325,
        704,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2326,
        705,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2327,
        706,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2328,
        707,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2329,
        708,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2330,
        709,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2331,
        710,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2332,
        711,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2333,
        712,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2334,
        713,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2335,
        714,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2336,
        715,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2337,
        716,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2338,
        717,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2339,
        718,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2340,
        719,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2341,
        720,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2342,
        721,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2343,
        722,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2344,
        723,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2345,
        724,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2346,
        725,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2347,
        726,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2348,
        727,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2349,
        728,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2350,
        729,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2351,
        730,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2352,
        731,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2353,
        732,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2354,
        733,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2355,
        734,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2356,
        735,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2357,
        736,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2358,
        737,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2359,
        738,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2360,
        739,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2361,
        740,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2362,
        741,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2363,
        742,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2364,
        743,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2365,
        744,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2366,
        745,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2367,
        746,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2368,
        747,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2369,
        748,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2370,
        749,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2371,
        750,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2372,
        751,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2373,
        752,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2374,
        753,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2375,
        754,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2376,
        755,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2377,
        756,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2378,
        757,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2379,
        758,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2380,
        759,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2381,
        760,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2382,
        761,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2383,
        762,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2384,
        763,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2385,
        764,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2386,
        765,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2387,
        766,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2388,
        767,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2389,
        768,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2390,
        769,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2391,
        770,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2392,
        771,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2393,
        772,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2394,
        773,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2395,
        774,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2396,
        775,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2397,
        776,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2398,
        777,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2399,
        778,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2400,
        779,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2401,
        780,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2402,
        781,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2403,
        782,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2404,
        783,
        'Electricity',
        '2025-09-30',
        0.00,
        0.00,
        '2025-10-12 05:56:00',
        '2025-10-12 05:56:00'
    ),
    (
        2405,
        1,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2406,
        2,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2407,
        4,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2408,
        5,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2409,
        17,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2410,
        107,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2411,
        489,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2412,
        490,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2413,
        491,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2414,
        492,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2415,
        493,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2416,
        494,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2417,
        495,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2418,
        496,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2419,
        497,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2420,
        498,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2421,
        499,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2422,
        500,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2423,
        501,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2424,
        502,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2425,
        503,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2426,
        504,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2427,
        505,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2428,
        506,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2429,
        507,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2430,
        508,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2431,
        509,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2432,
        510,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2433,
        511,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2434,
        512,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2435,
        513,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2436,
        514,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2437,
        515,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2438,
        516,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2439,
        517,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2440,
        518,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2441,
        519,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2442,
        520,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2443,
        521,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2444,
        522,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2445,
        523,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2446,
        524,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2447,
        525,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2448,
        526,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2449,
        527,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2450,
        528,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2451,
        529,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2452,
        530,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2453,
        531,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2454,
        532,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2455,
        533,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2456,
        534,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2457,
        535,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2458,
        536,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2459,
        537,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2460,
        538,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2461,
        539,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2462,
        540,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2463,
        541,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2464,
        542,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2465,
        543,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2466,
        544,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2467,
        545,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2468,
        546,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2469,
        547,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2470,
        548,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2471,
        549,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2472,
        550,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2473,
        551,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2474,
        552,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2475,
        553,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2476,
        554,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2477,
        555,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2478,
        556,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2479,
        557,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2480,
        558,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2481,
        559,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2482,
        560,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2483,
        561,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2484,
        562,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2485,
        563,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2486,
        564,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2487,
        565,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2488,
        566,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2489,
        567,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2490,
        568,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2491,
        569,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2492,
        570,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2493,
        571,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2494,
        572,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2495,
        573,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2496,
        574,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2497,
        575,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2498,
        576,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2499,
        577,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2500,
        578,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2501,
        579,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2502,
        580,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2503,
        581,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2504,
        582,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2505,
        583,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2506,
        584,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2507,
        585,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2508,
        586,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2509,
        587,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2510,
        588,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2511,
        589,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2512,
        590,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2513,
        591,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2514,
        592,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2515,
        593,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2516,
        594,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2517,
        595,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2518,
        596,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2519,
        597,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2520,
        598,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2521,
        7,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2522,
        8,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2523,
        9,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2524,
        10,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2525,
        16,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2526,
        616,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2527,
        617,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2528,
        618,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2529,
        619,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2530,
        620,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2531,
        621,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2532,
        622,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2533,
        623,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2534,
        624,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2535,
        625,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2536,
        626,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2537,
        627,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2538,
        628,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2539,
        629,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2540,
        630,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2541,
        631,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2542,
        632,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2543,
        633,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2544,
        634,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2545,
        635,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2546,
        636,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2547,
        637,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2548,
        638,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2549,
        639,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2550,
        640,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2551,
        641,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2552,
        642,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2553,
        643,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2554,
        644,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2555,
        645,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2556,
        646,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2557,
        647,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2558,
        648,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2559,
        649,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2560,
        650,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2561,
        651,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2562,
        652,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2563,
        653,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2564,
        654,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2565,
        655,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2566,
        656,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2567,
        657,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2568,
        658,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2569,
        659,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2570,
        660,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2571,
        661,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2572,
        662,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2573,
        663,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2574,
        664,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2575,
        665,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2576,
        666,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2577,
        667,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2578,
        668,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2579,
        669,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2580,
        670,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2581,
        671,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2582,
        672,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2583,
        673,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2584,
        674,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2585,
        675,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2586,
        676,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2587,
        677,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2588,
        678,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2589,
        679,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2590,
        680,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2591,
        681,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2592,
        682,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2593,
        683,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2594,
        684,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2595,
        685,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2596,
        686,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2597,
        687,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2598,
        688,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2599,
        689,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2600,
        690,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2601,
        691,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2602,
        692,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2603,
        693,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2604,
        694,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2605,
        695,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2606,
        696,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2607,
        697,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2608,
        11,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2609,
        12,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2610,
        13,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2611,
        14,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2612,
        15,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2613,
        698,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2614,
        699,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2615,
        700,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2616,
        701,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2617,
        702,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2618,
        703,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2619,
        704,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2620,
        705,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2621,
        706,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2622,
        707,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2623,
        708,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2624,
        709,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2625,
        710,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2626,
        711,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2627,
        712,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2628,
        713,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2629,
        714,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2630,
        715,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2631,
        716,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2632,
        717,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2633,
        718,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2634,
        719,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2635,
        720,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2636,
        721,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2637,
        722,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2638,
        723,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2639,
        724,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2640,
        725,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2641,
        726,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2642,
        727,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2643,
        728,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2644,
        729,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2645,
        730,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2646,
        731,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2647,
        732,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2648,
        733,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2649,
        734,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2650,
        735,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2651,
        736,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2652,
        737,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2653,
        738,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2654,
        739,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2655,
        740,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2656,
        741,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2657,
        742,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2658,
        743,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2659,
        744,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2660,
        745,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2661,
        746,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2662,
        747,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2663,
        748,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2664,
        749,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2665,
        750,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2666,
        751,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2667,
        752,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2668,
        753,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2669,
        754,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2670,
        755,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2671,
        756,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2672,
        757,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2673,
        758,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2674,
        759,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2675,
        760,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2676,
        761,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2677,
        762,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2678,
        763,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2679,
        764,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2680,
        765,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2681,
        766,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2682,
        767,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2683,
        768,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2684,
        769,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2685,
        770,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2686,
        771,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2687,
        772,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2688,
        773,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2689,
        774,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2690,
        775,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2691,
        776,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2692,
        777,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2693,
        778,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2694,
        779,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2695,
        780,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2696,
        781,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2697,
        782,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    ),
    (
        2698,
        783,
        'Electricity',
        '2025-10-31',
        0.00,
        0.00,
        '2025-10-31 07:32:23',
        '2025-10-31 07:32:23'
    );

/* SQLINES DEMO *** ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */
;
/* SQLINES DEMO *** ODE=IFNULL(@OLD_SQL_MODE, '') */
;
/* SQLINES DEMO *** GN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */
;
/* SQLINES DEMO *** CTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/* SQLINES DEMO *** OTES=IFNULL(@OLD_SQL_NOTES, 1) */
;