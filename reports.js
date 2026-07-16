// Reports & Analytics Dashboard Module
function renderReportsDashboard(container) {
    const isDark = document.documentElement.classList.contains("dark");
    const labelColor = isDark ? "#94a3b8" : "#64748b";
    const gridColor = isDark ? "#1e293b" : "#f1f5f9";

    container.innerHTML = `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Reports & Advanced Analytics</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Export audited databases and review campus placement rate performance.</p>
            </div>
            
            <!-- Export Buttons -->
            <div class="flex items-center gap-2">
                <button onclick="simulateReportExport('PDF')" class="flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i data-lucide="file-text" class="w-4 h-4 text-rose-500"></i> Export PDF
                </button>
                <button onclick="simulateReportExport('Excel')" class="flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-500"></i> Export Excel
                </button>
                <button onclick="simulateReportExport('CSV')" class="flex items-center gap-1.5 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <i data-lucide="file" class="w-4 h-4 text-blue-500"></i> Export CSV
                </button>
            </div>
        </div>

        <!-- Analytical Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Training participation -->
            <div class="dashboard-card p-5 lg:col-span-6 flex flex-col justify-between">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Training bootcamp participation</h3>
                <div id="chart-reports-training" class="w-full"></div>
            </div>

            <!-- Placements progress stats -->
            <div class="dashboard-card p-5 lg:col-span-6 flex flex-col justify-between">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">Placement Success rates per month (%)</h3>
                <div id="chart-reports-success-rates" class="w-full"></div>
            </div>
        </div>

        <!-- Audit Summary List -->
        <div class="dashboard-card p-5 space-y-4">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800/80 pb-2">Academic Audit Metrics (2026 Batch)</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800/80 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Department Branch</th>
                            <th class="py-3 px-4">Enrolled Students</th>
                            <th class="py-3 px-4">Placed Count</th>
                            <th class="py-3 px-4">Placed Ratio (%)</th>
                            <th class="py-3 px-4">Average Package (CTC)</th>
                            <th class="py-3 px-4">Top Recruiters</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 font-medium text-slate-600 dark:text-slate-350">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-850 dark:text-slate-300">Computer Science (CSE)</td>
                            <td class="py-3.5 px-4">190</td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-500">185</td>
                            <td class="py-3.5 px-4">97.37%</td>
                            <td class="py-3.5 px-4">16.4 LPA</td>
                            <td class="py-3.5 px-4">Google, Microsoft, Amazon</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-850 dark:text-slate-300">Information Tech (IT)</td>
                            <td class="py-3.5 px-4">150</td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-500">142</td>
                            <td class="py-3.5 px-4">94.67%</td>
                            <td class="py-3.5 px-4">12.8 LPA</td>
                            <td class="py-3.5 px-4">Adobe, TCS, Google</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-850 dark:text-slate-300">Electronics & Comm (ECE)</td>
                            <td class="py-3.5 px-4">130</td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-500">118</td>
                            <td class="py-3.5 px-4">90.76%</td>
                            <td class="py-3.5 px-4">9.5 LPA</td>
                            <td class="py-3.5 px-4">Intel, Microsoft, TCS</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-850 dark:text-slate-300">Mechanical Engineering</td>
                            <td class="py-3.5 px-4">80</td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-500">48</td>
                            <td class="py-3.5 px-4">60.00%</td>
                            <td class="py-3.5 px-4">6.2 LPA</td>
                            <td class="py-3.5 px-4">Tata Motors, TCS, L&T</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;

    if (window.lucide) window.lucide.createIcons();

    // Chart Inits
    setTimeout(() => {
        // Bar Chart - Training participation
        new ApexCharts(document.querySelector("#chart-reports-training"), {
            series: [{ name: "Participation Rate (%)", data: [85, 78, 64, 45] }],
            chart: { type: "bar", height: 260, toolbar: { show: false }, foreColor: labelColor },
            colors: ["#7C3AED"],
            plotOptions: { bar: { horizontal: true, barHeight: "40%", borderRadius: 5 } },
            xaxis: { categories: ["CSE", "IT", "ECE", "Mech"] },
            grid: { borderColor: gridColor },
            tooltip: { theme: isDark ? "dark" : "light" }
        }).render();

        // Area Chart - Success rate per month
        new ApexCharts(document.querySelector("#chart-reports-success-rates"), {
            series: [{ name: "Success rate", data: [45, 62, 75, 84, 91, 95] }],
            chart: { type: "area", height: 260, toolbar: { show: false }, foreColor: labelColor },
            colors: ["#10B981"],
            xaxis: { categories: ["Feb", "Mar", "Apr", "May", "Jun", "Jul"] },
            grid: { borderColor: gridColor },
            stroke: { curve: "smooth", width: 3 },
            fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } },
            tooltip: { theme: isDark ? "dark" : "light" }
        }).render();
    }, 150);
}

function simulateReportExport(format) {
    app.showToast(`Auditing databases for export...`, "info");
    
    setTimeout(() => {
        app.showToast(`Report downloaded successfully in ${format} format!`, "success");
    }, 1200);
}

// Global exports
window.simulateReportExport = simulateReportExport;
