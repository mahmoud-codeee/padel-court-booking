# Software Requirements Document

## 1. Project Overview

The objective is to develop an online platform for booking padel courts. The platform consists of two main components:

- An **Admin Dashboard** for management.
- A **Client Interface** for customers.

## 2. Technology Stack

### 2.1 Backend

- The backend must be implemented using **Laravel**.
- No other backend framework is permitted.

### 2.2 Frontend

- The frontend implemented as a **web application (React)**
- Delivering a single system with excellent quality, attention to detail, and creative execution is preferred over delivering two systems of lower quality.

## 3. Functional Requirements

### 3.1 Admin Dashboard

- Manage an unlimited number of courts (add, edit, delete).
- Define working hours for each court.
- Close a single court, multiple courts, or all courts on specific days or dates.
- Set the price per hour.
- Create offers/discounts based on the number of hours booked (e.g., 1 hour = 10 SAR, 2 hours = 8 SAR per hour).
- View all bookings with filtering options by:
  - Court.
  - Date.
  - Booking status.
  - Payment method.
  - Customer phone number.

### 3.2 Client Interface

- No account creation/registration is required.
- Phone number is mandatory; name and email are optional.
- The client selects a date and then views the available time slots.
- The client can book more than one hour and across more than one day within the same booking transaction.

### 3.3 Booking Logic (Important Considerations)

- Court names are not displayed to the client.
- If multiple courts (e.g., 3 courts) are available at a given time slot, that time slot remains visible/available to clients until all courts at that time slot are fully booked.
- Upon booking confirmation, the system automatically and randomly assigns an available court to the booking.
- Booking of past time slots, closed periods, or unavailable periods must be prevented.

### 3.4 Payment

Two payment methods must be provided:

- **Pay on arrival** (cash/in-person payment).
- **Online payment** via the **Thawani** payment gateway.

The Thawani sandbox (test) environment can be used, available at:
https://thawani-ecommerce-technologies.stoplight.io/docs/thawani-api-commerce-e-thawani-api/5534c91789a48

## 4. Technical / Non-Functional Requirements

The project must adhere to the following:

- Professional and consistent design.
- Excellent user experience (UX).
- Modern user interface (UI).
- Well-organized, clean code.
- Scalability.
- Adherence to software engineering best practices.
- Input validation.
- Proper error handling.
- Correct and well-structured database design.
- Clean and well-documented API design.
- Attention to performance.

## 5. Evaluation Criteria

The project will be evaluated based on the following aspects:

### 5.1 Technical Aspect

- Code quality.
- Project structure/architecture.
- Database design.
- API quality.
- Security.
- Performance.

### 5.2 User Interface

- Design quality.
- Ease of use.
- User experience.
- Responsiveness across different screen sizes.

### 5.3 Software Engineering / Problem Solving

- Requirements analysis.
- Handling of different edge cases/scenarios.
- Logic for court/booking distribution.
- Price and offer calculation logic.

### 5.4 Implementation Quality

- Completeness of requirements.
- Absence of bugs/errors.
- Quality of details.
- Ease of running/deploying the project.

## 6. Deliverables and Submission

The submission must include:

- A **GitHub repository link**. The repository must be **public**. Submissions without a public repository will not be accepted.
- A **README file** that includes:
  - Steps to run the project.
  - Technologies used.
  - Admin dashboard login credentials.
  - Any additional notes.
- Deploying the project to a demo/live server is considered an additional advantage (not mandatory).

### Submission Method

Submission is made through a request from the admin dashboard; a submission option will appear on the request/order page.

## 7. Deadline

The final submission deadline is **Monday, 03/08/2026, at 4:00 PM**.

## 8. Important Notes

- There is no single correct solution; creative ideas and improvements that add value to the project are welcome.
- Implementation quality is more important than the number of features. A complete and polished project is preferred over a project with many features that are incomplete.
- The project will be tested practically, and the source code will be reviewed. Please ensure the code is well-organized, clear, and maintainable.
- The use of AI tools (such as Claude and Cursor) is allowed to help complete the required work in the least amount of time and effort, with the highest quality.

## Additional Development Guidelines

Refer to `technical-guidelines.md` for frontend performance, React best practices, and development concepts.
