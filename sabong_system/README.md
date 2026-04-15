# Saraet Cockpit Arena Management System
## Himamaylan City, Negros Occidental
### PHP + MySQL (XAMPP) — Cockpit Arena & Derby Management

---

## 📁 PROJECT STRUCTURE
```
sabong_system/
├── index.php              ← Public arena homepage
├── database.sql           ← Full database + seed data
├── config/db.php          ← Database connection
├── includes/
│   ├── auth.php           ← Session helpers
│   └── sidebar.php        ← Admin sidebar navigation
├── assets/admin.css       ← Shared admin styles
└── admin/
    ├── login.php          ← Staff login
    ├── logout.php
    ├── dashboard.php      ← Main dashboard
    ├── derbies.php        ← Derby event management
    ├── entries.php        ← Derby entry registration
    ├── matches.php        ← Fight/match scheduling & results
    ├── owners.php         ← Owner/handler registry
    ├── roosters.php       ← Rooster registry
    ├── breeds.php         ← Breed reference list
    ├── reports.php        ← Stats & leaderboards
    └── staff.php          ← Staff account management
```

---

## 🚀 INSTALLATION

1. Copy `sabong_system/` to `C:\xampp\htdocs\sabong_system\`
2. Start **Apache** and **MySQL** in XAMPP
3. Go to `http://localhost/phpmyadmin`
4. Create database: `sabong_arena_db`
5. Import `database.sql`
6. Visit: `http://localhost/sabong_system/`

---

## 🔑 LOGIN CREDENTIALS (password: Admin@1234)
| Email | Role |
|-------|------|
| owner@arena.com | Owner |
| manager@arena.com | Manager |
| referee@arena.com | Referee |
| cashier@arena.com | Cashier |

---

## ✅ SYSTEM MODULES

### Derby Management
- Create and manage derby events (Open, Local, Invitational, Fiesta)
- Set entry fees, prize pools, max entries, venue, date/time
- Track status: Upcoming → Registration Open → Ongoing → Completed

### Derby Entry Registration
- Register roosters per derby with owner assignment
- Assign Meron / Wala side
- Track entry fee payment status
- Auto-generate entry numbers per derby

### Match / Fight Management
- Schedule fights with Meron vs Wala assignments
- Assign referees per fight
- Record fight results (Meron/Wala/Draw/No Contest)
- Auto-updates win/loss records for roosters and owners

### Owner / Handler Registry
- Full owner profiles with contact info
- Track overall W/L/D record
- Link to all roosters and derby entries

### Rooster Registry
- Complete rooster profiles (breed, color, weight, leg color)
- Track career W/L/D record
- Status: Active / Retired / Deceased

### Reports & Statistics
- Fight result distribution (Meron vs Wala wins)
- Top 10 roosters leaderboard
- Top 10 owners leaderboard
- Derby summary table

---

*Saraet Cockpit Arena — Himamaylan City, Negros Occidental*
