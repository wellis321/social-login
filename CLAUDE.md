# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a social media login training simulator for Duke of Edinburgh Awards staff to use during job interview sessions. The application simulates the registration and login experience of major social media platforms (Twitter/X, Facebook, Instagram) in a safe, controlled environment.

**Target Audience**: Users who have never used social media platforms before
**Session Duration**: Training sessions are designed for 10-minute demonstrations
**Purpose**: Allow staff to practice and become familiar with social media registration/login flows without using actual social media accounts

## Technology Stack

- **Backend**: PHP with MySQL database
- **Frontend**: Should replicate the look and feel of real social media login/registration pages
- **Database**: MySQL for storing test user accounts

## Key Features

1. **Platform Selection**: Users can choose which social media platform to practice (Twitter/X, Facebook, or Instagram)
2. **Step-by-Step Guidance**: Each step of registration/login includes clear explanations of:
   - What the step is
   - Why it's necessary
   - Best practices for completing it
3. **Account Management**:
   - Users can delete their own test accounts
   - Users can reset their accounts
   - Admins can delete or reset any account
4. **Safe Testing Environment**: All data can be deleted after interview sessions

## Architecture Considerations

### User Experience Requirements
- Must closely replicate the actual UI/UX of Twitter/X, Facebook, and Instagram login/registration flows
- Clear, educational messaging at each step (remember: audience has zero social media experience)
- Simple navigation between different platform simulations
- Forgiving error handling with helpful guidance

### Database Design
- User accounts table(s) scoped by platform
- Admin user management capabilities
- Easy account deletion/reset functionality
- Consider session management for logged-in users

### Security Notes
- This is a training environment, not production
- Accounts are temporary and meant to be deleted
- Focus on UI replication rather than production-grade security
- No real social media API integration needed

## Development Guidelines

### Platform Simulation Accuracy
When implementing login/registration flows:
- Study the current UI/UX of each platform
- Replicate visual styling (colors, fonts, layouts)
- Mirror the step sequence (email/phone → password → verification, etc.)
- Include common error scenarios (wrong password, account exists, etc.)

### Educational Content
Every form field and step should include:
- A clear label explaining what information is needed
- Helper text explaining why this information is required
- Tips for best practices (strong passwords, recognizing phishing, etc.)

### Admin Functionality
Admin interface should support:
- Viewing all test accounts across all platforms
- Bulk deletion of accounts
- Account reset (clear data but keep account)
- Platform-specific account management

## Project Structure

Since this is a PHP/MySQL application, organize code as:
- `/public` or `/www` - Entry point and public assets
- `/includes` or `/src` - PHP business logic and database connections
- `/templates` or `/views` - HTML templates for each platform's pages
- `/assets` - CSS, JavaScript, images for each platform's styling
- `/admin` - Admin panel for account management
- `/config` - Database configuration and settings

## Common Tasks

### Database Setup
Create MySQL database with tables for:
- Users (one table per platform, or unified with platform field)
- Sessions (if implementing login persistence)
- Admin users (if separate from regular test accounts)

### Adding a New Platform
1. Create template files for login/registration flows
2. Add platform-specific CSS styling
3. Update platform selection page
4. Add database schema for platform-specific fields
5. Implement educational tooltips/guidance for each step

### Testing
Manually test each platform's flow ensuring:
- All steps match the real platform experience
- Educational content is clear and helpful
- Error messages are instructive
- Account deletion/reset works correctly
- Admin functions work across all platforms
