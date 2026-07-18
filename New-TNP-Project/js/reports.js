// Reports & Analytics Dashboard — TPMS v2.0 Premium
function renderReportsDashboard(container) {
    const isDark    = document.documentElement.classList.contains("dark");
    const gridColor = isDark ? "#1e293b" : "#f1f5f9";
    const labelColor= isDark ? "#94a3b8" : "#64748b";

    container.innerHTML = `
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-[var(--text-primary)]">Reports &amp; Analytics</h1>
                <p class="text-sm text-[var(--text-faint)] mt-0.5">Academic year placement performance, branch comparisons, and package analytics.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="app.showToast('PDF report export queued!', 'success')"
                        class="btn btn-primary btn-sm shadow-[var(--shadow-brand)]">
                    <i data-lucide="download" class="w-4 h-4"></i> Export PDF
                </button>
            </div>
        </div>

        <!-- Summary KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger-children">
            ${[
                { label: "Total Students",    value: "5,420", trend: "+4.2%",  icon: "users",       color: "var(--primary)",   accent: "var(--primary-light)" },
                { label: "Placed Students",   value: "4,210", trend: "+8.5%",  icon: "check-circle",color: "var(--success)",   accent: "var(--success-light)" },
                { label: "Avg. Package",      value: "₹14.2L",trend: "+12.3%", icon: "trending-up", color: "var(--accent)",    accent: "rgba(6,182,212,0.10)" },
                { label: "Highest Package",   value: "₹48.2L",trend: "+6.1%",  icon: "award",       color: "var(--warning)",   accent: "var(--warning-light)" }
            ].map(k => `
                <div class="dashboard-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-[var(--text-faint)]">${k.label}</p>
                            <h3 class="text-2xl font-extrabold mt-1.5 text-[var(--text-primary)] tracking-tight">${k.value}</h3>
                            <p class="flex items-center gap-1 text-[11px] mt-1 font-semibold text-[var(--success)]">
                                <i data-lucide="trending-up" class="w-3 h-3"></i> ${k.trend} YoY
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0" style="background:${k.accent};">
                            <i data-lucide="${k.icon}" class="w-4.5 h-4.5" style="color:${k.color};"></i>
                        </div>
                    </div>
                </div>
            `).join("")}
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="dashboard-card p-5">
                <div class="mb-5">
                    <h3 class="font-bold text-sm text-[var(--text-primary)]">Department-wise Placement %</h3>
                    <p class="text-xs text-[var(--text-faint)] mt-0.5">Breakdown by academic branch</p>
                </div>
                <div id="chart-rpt-dept"></div>
            </div>

            <div class="dashboard-card p-5">
                <div class="mb-5">
                    <h3 class="font-bold text-sm text-[var(--text-primary)]">Package Distribution</h3>
                    <p class="text-xs text-[var(--text-faint)] mt-0.5">Students across salary brackets (LPA)</p>
                </div>
                <div id="chart-rpt-package"></div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="dashboard-card p-5 lg:col-span-2">
                <div class="mb-5">
                    <h3 class="font-bold text-sm text-[var(--text-primary)]">Industry-wise Hiring Share</h3>
                    <p class="text-xs text-[var(--text-faint)] mt-0.5">Offers by corporate sector</p>
                </div>
                <div id="chart-rpt-industry"></div>
            </div>

            <div class="dashboard-card p-5">
                <div class="mb-5">
                    <h3 class="font-bold text-sm text-[var(--text-primary)]">Placement Category Split</h3>
                    <p class="text-xs text-[var(--text-faint)] mt-0.5">Overall outcome breakdown</p>
                </div>
                <div id="chart-rpt-outcome"></div>
            </div>
        </div>

        <!-- Company Hires Table -->
        <div class="dashboard-card overflow-hidden">
            <div class="flex items-center justify-between p-5 border-b border-[var(--border-color)]">
                <div>
                    <h3 class="font-bold text-sm text-[var(--text-primary)]">Top Hiring Partners — ${new Date().getFullYear()}</h3>
                    <p class="text-xs text-[var(--text-faint)] mt-0.5">Ranked by total offers extended</p>
                </div>
                <button onclick="app.showToast('Export started!', 'success')"
                        class="btn btn-secondary btn-sm">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i> Export CSV
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Company</th>
                            <th>Industry</th>
                            <th>Offers</th>
                            <th>Avg CTC (LPA)</th>
                            <th>Highest CTC (LPA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${[
                            { n: "Google",    ind: "Product Technology", offers: 42, avg: 28.4, high: 48.2, color: "var(--primary)" },
                            { n: "Microsoft", ind: "Product Technology", offers: 38, avg: 22.6, high: 45.0, color: "var(--secondary)" },
                            { n: "Amazon",    ind: "E-Commerce & Cloud", offers: 55, avg: 18.2, high: 36.8, color: "var(--warning)" },
                            { n: "Infosys",   ind: "IT Services",        offers: 120, avg: 8.4, high: 14.5, color: "var(--accent)" },
                            { n: "TCS",       ind: "IT Services",        offers: 145, avg: 7.2, high: 12.0, color: "var(--success)" },
                            { n: "HCL",       ind: "IT Consulting",      offers: 85,  avg: 9.8, high: 16.5, color: "var(--info)" }
                        ].map((row, i) => `
                            <tr>
                                <td><span class="font-bold text-[var(--text-faint)]">${i + 1}</span></td>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg gradient-bg flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            ${row.n.charAt(0)}
                                        </div>
                                        <span class="font-bold text-[var(--text-primary)]">${row.n}</span>
                                    </div>
                                </td>
                                <td class="text-[var(--text-muted)]">${row.ind}</td>
                                <td><span class="badge badge-primary font-bold">${row.offers}</span></td>
                                <td class="font-bold text-[var(--text-primary)]">₹${row.avg}</td>
                                <td class="font-bold" style="color:${row.color};">₹${row.high}</td>
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            </div>
        </div>
    `;

    if (window.lucide) window.lucide.createIcons();

    // Render all charts
    setTimeout(() => {
        const deptData = mockData.analytics.departmentPlacements;

        new ApexCharts(document.querySelector("#chart-rpt-dept"), {
            series: deptData.placementPct,
            chart: { type: "radialBar", height: 280, foreColor: labelColor, background: "transparent" },
            plotOptions: { radialBar: { hollow: { size: "55%" }, track: { background: isDark ? "#1e293b" : "#f1f5f9" }, dataLabels: { name: { fontSize: "12px" }, value: { fontSize: "16px", fontWeight: "700", formatter: v => v + "%" } } } },
            labels: deptData.branches,
            colors: ["#4F46E5","#7C3AED","#06B6D4","#10B981"],
            legend: { show: true, position: "bottom", fontSize: "11px" },
            stroke: { lineCap: "round" }
        }).render();

        new ApexCharts(document.querySelector("#chart-rpt-package"), {
            series: [{ name: "Students", data: [45, 120, 210, 185, 80, 35] }],
            chart: { type: "bar", height: 280, toolbar: { show: false }, foreColor: labelColor, background: "transparent" },
            colors: ["#4F46E5"],
            plotOptions: { bar: { columnWidth: "50%", borderRadius: 6 } },
            xaxis: { categories: ["3-6L","6-10L","10-15L","15-25L","25-35L","35L+"], axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { formatter: v => v + " students" } },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            dataLabels: { enabled: false },
            tooltip: { theme: isDark ? "dark" : "light" }
        }).render();

        new ApexCharts(document.querySelector("#chart-rpt-industry"), {
            series: [{ name: "Students Placed", data: [180, 145, 98, 65, 42, 30] }],
            chart: { type: "bar", height: 270, toolbar: { show: false }, foreColor: labelColor, background: "transparent" },
            colors: ["#7C3AED"],
            plotOptions: { bar: { horizontal: true, barHeight: "50%", borderRadius: 6 } },
            xaxis: { axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { categories: ["IT Services","Product Tech","E-Commerce","Consulting","Finance","Core Eng"] },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            dataLabels: { enabled: false },
            tooltip: { theme: isDark ? "dark" : "light" }
        }).render();

        new ApexCharts(document.querySelector("#chart-rpt-outcome"), {
            series: [77.6, 14.3, 4.5, 3.6],
            chart: { type: "pie", height: 270, foreColor: labelColor, background: "transparent" },
            labels: ["Placed","Higher Studies","Not Interested","In Progress"],
            colors: ["#10B981","#4F46E5","#94A3B8","#F59E0B"],
            legend: { position: "bottom", fontSize: "11px", markers: { width: 8, height: 8, radius: 4 } },
            stroke: { show: false },
            dataLabels: { style: { fontSize: "12px", fontWeight: "700" } },
            tooltip: { theme: isDark ? "dark" : "light" }
        }).render();
    }, 200);
}
