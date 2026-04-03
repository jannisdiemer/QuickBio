1. Introduction
QuickBio is a web-based platform designed to simplify the creation of personal profile pages.
The goal was to build a lightweight, fast and user-friendly alternative to traditional "link-in-bio" tools, while maintaining full control over design and functionality

2. Problem
Many existing profile tools are either overloaded with unnecessary features or lack flexibility in customization.
This often leads to slow performance and limited user control.

3. Solution
QuickBio focuses on simplicity and usability by providing essential features such as intuitive profile editing and clean, pre-designed layouts.
The platform is designed to deliver a fast and straightforward user experience without unnecessary complexity.

4. Features
- Custom personal profile pages with unique URLs  
- Social media integration (Instagram, Twitter, etc.)  
- Full account management (create, edit, delete)  
- Built-in contact system with email functionality  
- Email verification system  
- Customizable profile layouts  
- Custom PHP-based routing system (clean URLs, centralized handling)  

5. Tech Stack & Decisions
## Why PHP?
I chose PHP due to its simplicity, flexibility, and seamless integration with HTML.
It enabled rapid development while keeping the overall system lightweight and easy to maintain.

## Why custom routing?
A custom routing system was implemented to enable clean URLs (e.g. /username) and centralized request handling through a single entry point.

## Why no framework?
Instead of using a framework, I built a custom backend structure to maintain full control over the application logic and reduce overhead.

6. Architecture
The application follows a simple client-server architecture:

Client (HTML, CSS, JavaScript, Bootstrap)
↓
PHP Backend (routing, logic, sessions)
↓
MySQL Database

- The server runs on Ubuntu with Plesk  
- PHP sessions are used for authentication and state management  
- The database is powered by MySQL (managed via phpMyAdmin)  

7. Security
- Prepared statements (PDO) to prevent SQL injection  
- Input validation and sanitization  
- XSS protection using htmlspecialchars  

8. Challenges
One of the main challenges was handling email deliverability and embedding profile images in outgoing emails.

This required configuring correct SPF and DKIM records in the domain's DNS settings. Due to changing SMTP server configurations from the hosting provider, multiple adjustments were necessary.

Another challenge was implementing a clean routing system using a single entry point (index.php), ensuring all requests were handled consistently without breaking page logic.

9. What I Learned
- Building secure backend systems  
- Handling real-world email infrastructure (SPF, DKIM)  
- Designing scalable PHP application structures  
- Implementing custom routing without frameworks  

10. Future Improvements
- Improved UI
- Dashboard with analytics
- Admin page
- Additional design templates