-- Create the database
CREATE DATABASE IF NOT EXISTS stu_and_tea_registration;

-- Use the newly created database
USE stu_and_tea_registration;

-- Create the students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    dob DATE NOT NULL,
    date_of_admission DATE NOT NULL,
    address TEXT NOT NULL
);

-- Create the teachers table
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    dob DATE NOT NULL,
    date_of_admission DATE NOT NULL,
    address TEXT NOT NULL
);
