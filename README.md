# PHP Wobble Fump

This repository contains solutions to several issues. Each issue has its own folder with its implementation, relevant scripts, and detailed documentation.

---

## Contents

1. [Q1: Memory Limitation in Chunk-Based Processing](#q1-memory-limitation-in-chunk-based-processing)
2. [Q2: Symfony Console Commands](#q2-symfony-console-commands)
3. [Q3: MySQL Table Schema and Queries](#q3-mysql-table-schema-and-queries)

---

## Q1: Memory Limitation in Chunk-Based Processing

### Overview

This solution addresses memory limitations during the processing of large files in chunks. The goal is to ensure that memory usage remains bounded and independent of the file size.

### Solution Highlights

- Implemented using Symfony and PHP.
- Includes commands for running tests and processing large files with Fourier Transforms.

### Resources

- [Q1 Solution README](./Q1_solution/php_impl/php_impl/README.md)

---

## Q2: Symfony Console Commands

### Overview

This issue demonstrates how to create custom Symfony console commands. Examples include commands for executing cURL requests.

### Solution Highlights

- Includes detailed instructions for creating and registering Symfony commands.
- Supports various Symfony versions.

### Resources

- [Q2 Solution README](./Q2_solution/README.md)

---

## Q3: MySQL Table Schema and Queries

### Overview

This issue involves working with a provided CSV dataset to create a MySQL database schema, load data, and write queries to analyze user activity.

### Solution Highlights

- Defines a MySQL schema for user activity data.
- Provides SQL queries to:
  - Find the top 10 most active users in the last 180 days.
  - Retrieve the latest activity for each user.

### Resources

- [Q3 Solution README](./Q3_solution/README.md)
- [Setup Script for Q3](./Q3_solution/setup_user_activity.sql)
- [CSV Dataset for Q3](./Q3_solution/user_downloads.csv)

---

## Usage

### General Setup

1. Clone the repository:
   ```bash
   git clone <repository_url>
   cd php_wobble_fump
