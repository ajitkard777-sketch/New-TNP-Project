// Mock Database for TPMS
const mockData = {
    // Current user profiles details
    currentStudent: {
        id: "STU2026001",
        name: "Aarav Sharma",
        email: "aarav.sharma@college.edu",
        avatar: "https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&q=80&w=120",
        branch: "Computer Science & Engineering",
        cgpa: 9.24,
        backlogs: 0,
        skills: ["React.js", "Node.js", "Python", "MongoDB", "Data Structures", "Tailwind CSS"],
        resumeUploaded: true,
        resumeName: "Aarav_Sharma_Resume.pdf",
        placementStatus: "In Progress", // "In Progress" | "Placed" | "Not Interested"
        appliedJobs: ["JOB001", "JOB003"],
        bookmarkedJobs: ["JOB002"],
        registeredTraining: ["TRN001", "TRN003"],
        universityApplications: ["UNI002"],
        profileCompletion: 85
    },

    // User Session Configuration (default)
    session: {
        role: "guest", // "guest" | "student" | "company" | "admin"
        userId: null
    },

    // Activity Log
    activities: [
        { id: 1, type: "placement", text: "Rahul Verma (ECE) selected at Microsoft - CTC 45 LPA", time: "10 mins ago", icon: "award" },
        { id: 2, type: "job", text: "Google posted new role: Associate Software Engineer", time: "1 hour ago", icon: "briefcase" },
        { id: 3, type: "interview", text: "Interview scheduled for Sneha Reddy with Amazon", time: "3 hours ago", icon: "calendar" },
        { id: 4, type: "registration", text: "Intel Corp registered as a new recruiter partner", time: "5 hours ago", icon: "user-plus" },
        { id: 5, type: "training", text: "New training created: Full Stack Web Dev bootcamp", time: "1 day ago", icon: "book-open" },
        { id: 6, type: "placement", text: "Priyan Patel (CSE) selected at Adobe - CTC 28 LPA", time: "1 day ago", icon: "award" }
    ],

    // Placement / Jobs Database
    jobs: [
        {
            id: "JOB001",
            companyId: "COMP001",
            companyName: "Google",
            companyLogo: "https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg",
            title: "Software Engineer - L3",
            package: "32.5 LPA",
            location: "Bangalore, India",
            eligibility: "B.Tech CSE/IT, CGPA >= 8.0, 0 Backlogs",
            skills: ["Java", "C++", "System Design", "Algorithms"],
            deadline: "2026-07-25",
            status: "Active",
            description: "We are looking for Software Engineers to join our core infrastructure and product teams. You will work on massive scale systems and build features used by billions of users worldwide."
        },
        {
            id: "JOB002",
            companyId: "COMP002",
            companyName: "Microsoft",
            companyLogo: "https://upload.wikimedia.org/wikipedia/commons/9/96/Microsoft_logo_%282012%29.svg",
            title: "Azure Cloud Consultant",
            package: "26.0 LPA",
            location: "Hyderabad (Hybrid)",
            eligibility: "B.Tech CSE/IT/ECE, CGPA >= 7.5",
            skills: ["C#", "Cloud Computing", "Networking", "Azure"],
            deadline: "2026-07-28",
            status: "Active",
            description: "Join the Microsoft Customer Success Unit. In this role, you will design and implement enterprise cloud systems for fortune 500 companies migrating infrastructure to Azure."
        },
        {
            id: "JOB003",
            companyId: "COMP003",
            companyName: "Amazon",
            companyLogo: "https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg",
            title: "Systems Analyst Intern",
            package: "18.0 LPA",
            location: "Pune, India",
            eligibility: "B.Tech / M.Tech / MCA, CGPA >= 7.0",
            skills: ["SQL", "Python", "Data Pipelines", "AWS"],
            deadline: "2026-07-22",
            status: "Active",
            description: "Amazon Operation Technology team is hiring interns to optimize warehouse delivery tracking and backend analytics databases. Strong SQL skills are required."
        },
        {
            id: "JOB004",
            companyId: "COMP004",
            companyName: "TCS",
            companyLogo: "https://upload.wikimedia.org/wikipedia/commons/b/b1/Tata_Consultancy_Services_Logo.svg",
            title: "Digital Software Developer",
            package: "7.5 LPA",
            location: "Chennai, India",
            eligibility: "All Branches, CGPA >= 6.0",
            skills: ["JavaScript", "HTML/CSS", "DBMS", "Java"],
            deadline: "2026-08-05",
            status: "Active",
            description: "TCS Digital is hiring entry level consultants. You will undergo an initial 3-month digital training program in modern full-stack development, cloud computing, or cybersecurity."
        },
        {
            id: "JOB005",
            companyId: "COMP005",
            companyName: "Adobe",
            companyLogo: "https://upload.wikimedia.org/wikipedia/commons/8/8d/Adobe_Systems_logo_and_wordmark.svg",
            title: "UI/UX Developer",
            package: "22.0 LPA",
            location: "Noida, India",
            eligibility: "B.Tech / B.Des, CGPA >= 7.0",
            skills: ["Figma", "CSS/Tailwind", "JavaScript", "React.js"],
            deadline: "2026-07-19",
            status: "Active",
            description: "Adobe Creative Cloud team is looking for a front-end UI/UX Developer with high aesthetic taste. Work directly with designers and implement responsive mockups."
        }
    ],

    // Recruiter / Companies Database
    companies: [
        { id: "COMP001", name: "Google", registeredDate: "2022-03-12", website: "https://careers.google.com", industry: "Technology", jobCount: 3, contact: "hr-in@google.com" },
        { id: "COMP002", name: "Microsoft", registeredDate: "2022-01-15", website: "https://careers.microsoft.com", industry: "Technology/Cloud", jobCount: 2, contact: "indiajobs@microsoft.com" },
        { id: "COMP003", name: "Amazon", registeredDate: "2023-05-19", website: "https://amazon.jobs", industry: "E-Commerce/Web Services", jobCount: 4, contact: "in-recruitment@amazon.com" },
        { id: "COMP004", name: "TCS", registeredDate: "2021-08-01", website: "https://tcs.com/careers", industry: "IT Services", jobCount: 8, contact: "campus.hiring@tcs.com" },
        { id: "COMP005", name: "Adobe", registeredDate: "2023-11-10", website: "https://adobe.com/careers", industry: "Creative Software", jobCount: 1, contact: "talent@adobe.com" }
    ],

    // Training Modules
    training: [
        {
            id: "TRN001",
            title: "Advanced Data Structures & Algorithms",
            trainer: "Dr. Rajesh K. (Ex-Amazon Architect)",
            date: "Every Sat-Sun (July 10 - Aug 15)",
            duration: "30 Hours",
            status: "Ongoing",
            description: "Rigorous training covers Graphs, Dynamic Programming, String Algorithms, and Competitive Programming problem solving."
        },
        {
            id: "TRN002",
            title: "Full Stack Development with MERN Stack",
            trainer: "Vikram Malhotra (Senior Engineer, TechVeda)",
            date: "Mon-Wed-Fri (July 15 - Sept 15)",
            duration: "60 Hours",
            status: "Upcoming",
            description: "Build production-ready applications with MongoDB, Express.js, React, Node.js, Redux, and Tailwind. Includes final capstone project."
        },
        {
            id: "TRN003",
            title: "System Design (Lld & Hld) Foundations",
            trainer: "Suresh Pillai (Technical Director, Intel)",
            date: "July 24, 25 & 26",
            duration: "12 Hours",
            status: "Upcoming",
            description: "Crash course on Microservices, Caching (Redis), Load Balancers, Database Sharding, Message Queues (Kafka), and OOP Design Patterns."
        },
        {
            id: "TRN004",
            title: "Aptitude, Soft Skills & Resume Building",
            trainer: "Prof. Anjali Mehta (TPC Advisor)",
            date: "Completed (June 01 - June 20)",
            duration: "20 Hours",
            status: "Completed",
            description: "Covers quantitative aptitude, logical reasoning, verbal ability, resume formatting, mock HR interviews, and salary negotiation skills."
        }
    ],

    // Higher Studies Universities
    universities: [
        {
            id: "UNI001",
            logo: "https://upload.wikimedia.org/wikipedia/commons/0/07/Harvard_university_shield.svg",
            name: "Harvard University",
            country: "USA",
            courses: "M.S. in Computer Science, Ph.D. in AI",
            deadline: "2026-12-15",
            scholarship: "Partial Scholarship Available (upto 50%)",
            fees: "$58,000 / year",
            ranking: "QS Rank 4",
            minCGPA: 8.5,
            website: "https://www.harvard.edu/admissions"
        },
        {
            id: "UNI002",
            logo: "https://upload.wikimedia.org/wikipedia/commons/a/a4/Seal_of_Stanford_University.svg",
            name: "Stanford University",
            country: "USA",
            courses: "M.S. in Software Engineering, M.S. in Data Science",
            deadline: "2026-11-30",
            scholarship: "Fully Funded Fellowship Available",
            fees: "$62,000 / year",
            ranking: "QS Rank 3",
            minCGPA: 8.0,
            website: "https://gradadmissions.stanford.edu"
        },
        {
            id: "UNI003",
            logo: "https://upload.wikimedia.org/wikipedia/en/b/b5/Imperial_College_London_Crest.svg",
            name: "Imperial College London",
            country: "UK",
            courses: "M.Sc. in Advanced Computing, M.Sc. in Machine Learning",
            deadline: "2026-10-15",
            scholarship: "Commonwealth Scholarships Eligible",
            fees: "£36,500 / year",
            ranking: "QS Rank 6",
            minCGPA: 7.5,
            website: "https://www.imperial.ac.uk/study/pg"
        },
        {
            id: "UNI004",
            logo: "https://upload.wikimedia.org/wikipedia/commons/e/e0/National_University_of_Singapore_logo_and_seal.svg",
            name: "National University of Singapore (NUS)",
            country: "Singapore",
            courses: "Master of Computing, M.Sc. in Quantitative Finance",
            deadline: "2026-09-30",
            scholarship: "Tuition Fee Grant Available",
            fees: "S$48,000 / year",
            ranking: "QS Rank 8",
            minCGPA: 7.0,
            website: "https://nus.edu.sg/admissions"
        },
        {
            id: "UNI005",
            logo: "https://upload.wikimedia.org/wikipedia/commons/c/c3/Seal_of_the_University_of_Melbourne.svg",
            name: "University of Melbourne",
            country: "Australia",
            courses: "Master of Information Technology",
            deadline: "2026-11-15",
            scholarship: "Melbourne Graduate Scholarship Available",
            fees: "A$52,000 / year",
            ranking: "QS Rank 14",
            minCGPA: 6.5,
            website: "https://study.unimelb.edu.au"
        }
    ],

    // Job Application Pipeline Database (For tracker)
    applications: [
        {
            id: "APP001",
            studentId: "STU2026001",
            studentName: "Aarav Sharma",
            studentCGPA: 9.24,
            studentBranch: "Computer Science & Engineering",
            studentResume: "Aarav_Sharma_Resume.pdf",
            jobId: "JOB001",
            jobTitle: "Software Engineer - L3",
            companyName: "Google",
            appliedDate: "2026-07-12",
            status: "Interview", // "Applied" | "Under Review" | "Shortlisted" | "Interview" | "Selected" | "Rejected"
            timeline: [
                { stage: "Applied", date: "July 12, 2026", done: true },
                { stage: "Under Review", date: "July 13, 2026", done: true },
                { stage: "Shortlisted", date: "July 14, 2026", done: true },
                { stage: "Interview", date: "Scheduled: July 20, 2026", done: false },
                { stage: "Selected", date: "TBD", done: false }
            ]
        },
        {
            id: "APP002",
            studentId: "STU2026001",
            studentName: "Aarav Sharma",
            studentCGPA: 9.24,
            studentBranch: "Computer Science & Engineering",
            studentResume: "Aarav_Sharma_Resume.pdf",
            jobId: "JOB003",
            jobTitle: "Systems Analyst Intern",
            companyName: "Amazon",
            appliedDate: "2026-07-14",
            status: "Under Review",
            timeline: [
                { stage: "Applied", date: "July 14, 2026", done: true },
                { stage: "Under Review", date: "July 15, 2026", done: true },
                { stage: "Shortlisted", date: "Pending", done: false },
                { stage: "Interview", date: "TBD", done: false },
                { stage: "Selected", date: "TBD", done: false }
            ]
        },
        {
            id: "APP003",
            studentId: "STU2026002",
            studentName: "Sneha Reddy",
            studentCGPA: 8.85,
            studentBranch: "Electronics & Communication",
            studentResume: "Sneha_Reddy_Resume.pdf",
            jobId: "JOB002",
            jobTitle: "Azure Cloud Consultant",
            companyName: "Microsoft",
            appliedDate: "2026-07-11",
            status: "Shortlisted",
            timeline: [
                { stage: "Applied", date: "July 11, 2026", done: true },
                { stage: "Under Review", date: "July 12, 2026", done: true },
                { stage: "Shortlisted", date: "July 15, 2026", done: true },
                { stage: "Interview", date: "Pending", done: false },
                { stage: "Selected", date: "TBD", done: false }
            ]
        },
        {
            id: "APP004",
            studentId: "STU2026003",
            studentName: "Rahul Verma",
            studentCGPA: 8.92,
            studentBranch: "Electronics & Communication",
            studentResume: "Rahul_Verma_Resume.pdf",
            jobId: "JOB002",
            jobTitle: "Azure Cloud Consultant",
            companyName: "Microsoft",
            appliedDate: "2026-07-10",
            status: "Selected",
            timeline: [
                { stage: "Applied", date: "July 10, 2026", done: true },
                { stage: "Under Review", date: "July 11, 2026", done: true },
                { stage: "Shortlisted", date: "July 12, 2026", done: true },
                { stage: "Interview", date: "July 14, 2026", done: true },
                { stage: "Selected", date: "July 16, 2026", done: true }
            ]
        },
        {
            id: "APP005",
            studentId: "STU2026004",
            studentName: "Rohan Das",
            studentCGPA: 7.20,
            studentBranch: "Mechanical Engineering",
            studentResume: "Rohan_Das_CV.pdf",
            jobId: "JOB004",
            jobTitle: "Digital Software Developer",
            companyName: "TCS",
            appliedDate: "2026-07-15",
            status: "Applied",
            timeline: [
                { stage: "Applied", date: "July 15, 2026", done: true },
                { stage: "Under Review", date: "Pending", done: false },
                { stage: "Shortlisted", date: "Pending", done: false },
                { stage: "Interview", date: "TBD", done: false },
                { stage: "Selected", date: "TBD", done: false }
            ]
        }
    ],

    // Student Registry
    students: [
        { id: "STU2026001", name: "Aarav Sharma", cgpa: 9.24, branch: "Computer Science", resume: "Aarav_Sharma_Resume.pdf", status: "In Progress" },
        { id: "STU2026002", name: "Sneha Reddy", cgpa: 8.85, branch: "Electronics & Comm", resume: "Sneha_Reddy_Resume.pdf", status: "In Progress" },
        { id: "STU2026003", name: "Rahul Verma", cgpa: 8.92, branch: "Electronics & Comm", resume: "Rahul_Verma_Resume.pdf", status: "Placed" },
        { id: "STU2026004", name: "Rohan Das", cgpa: 7.20, branch: "Mechanical Eng", resume: "Rohan_Das_CV.pdf", status: "In Progress" },
        { id: "STU2026005", name: "Kriti Sen", cgpa: 9.51, branch: "Computer Science", resume: "Kriti_Sen_Resume.pdf", status: "Placed" }
    ],

    // Analytics Chart Data Matrices
    analytics: {
        placementTrend: {
            years: [2021, 2022, 2023, 2024, 2025, 2026],
            rates: [82, 88, 91, 89, 93, 95]
        },
        departmentPlacements: {
            branches: ["CSE", "IT", "ECE", "EEE", "Mech", "Civil"],
            placedCount: [185, 142, 118, 75, 48, 22],
            totalCount: [190, 150, 130, 95, 80, 60],
            // Derived: placement percentage per branch (for radial bar chart in reports)
            placementPct: [97, 95, 91, 79, 60, 37]
        },
        companyHiring: {
            names: ["Google", "Microsoft", "Amazon", "Adobe", "TCS", "Others"],
            counts: [15, 22, 30, 8, 125, 84]
        },
        monthlyRegistrations: {
            months: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"],
            studentRegistrations: [450, 890, 1200, 1800, 2400, 3100, 3450],
            companyRegistrations: [20, 45, 62, 85, 110, 145, 180]
        }
    }
};

// Expose mockData globally
window.mockData = mockData;
