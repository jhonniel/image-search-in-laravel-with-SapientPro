@extends('layouts.admin')

@section('title', 'Dashboard - FindITFast Admin')

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Reports Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-file-search text-pink-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">1,380</h3>
                            <p class="text-sm text-gray-600">Total Reports</p>
                        </div>
                    </div>
                    <div class="flex items-center text-green-600">
                        <i class="fas fa-arrow-up text-sm mr-1"></i>
                        <span class="text-sm font-medium">30%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items in Progress Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-purple-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">21</h3>
                            <p class="text-sm text-gray-600">Items in progress</p>
                        </div>
                    </div>
                    <div class="flex items-center text-red-600">
                        <i class="fas fa-arrow-down text-sm mr-1"></i>
                        <span class="text-sm font-medium">83%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contributors Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-gift text-pink-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">729</h3>
                            <p class="text-sm text-gray-600">Contributors</p>
                        </div>
                    </div>
                    <div class="flex items-center text-green-600">
                        <i class="fas fa-arrow-up text-sm mr-1"></i>
                        <span class="text-sm font-medium">11%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bar Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Lost vs Found Items</h3>
            <div class="h-64 flex items-end space-x-2">
                <!-- Chart bars for each month -->
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-6 bg-pink-primary rounded-t" style="height: 80px;"></div>
                        <div class="w-6 bg-blue-primary rounded-t" style="height: 120px;"></div>
                    </div>
                    <span class="text-xs text-gray-600">Jan</span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-6 bg-pink-primary rounded-t" style="height: 100px;"></div>
                        <div class="w-6 bg-blue-primary rounded-t" style="height: 140px;"></div>
                    </div>
                    <span class="text-xs text-gray-600">Feb</span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-6 bg-pink-primary rounded-t" style="height: 90px;"></div>
                        <div class="w-6 bg-blue-primary rounded-t" style="height: 130px;"></div>
                    </div>
                    <span class="text-xs text-gray-600">Mar</span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-6 bg-pink-primary rounded-t" style="height: 70px;"></div>
                        <div class="w-6 bg-blue-primary rounded-t" style="height: 90px;"></div>
                    </div>
                    <span class="text-xs text-gray-600">Apr</span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-6 bg-pink-primary rounded-t" style="height: 60px;"></div>
                        <div class="w-6 bg-blue-primary rounded-t" style="height: 80px;"></div>
                    </div>
                    <span class="text-xs text-gray-600">May</span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-6 bg-pink-primary rounded-t" style="height: 110px;"></div>
                        <div class="w-6 bg-blue-primary rounded-t" style="height: 150px;"></div>
                    </div>
                    <span class="text-xs text-gray-600">Jun</span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-6 bg-pink-primary rounded-t" style="height: 85px;"></div>
                        <div class="w-6 bg-blue-primary rounded-t" style="height: 95px;"></div>
                    </div>
                    <span class="text-xs text-gray-600">Jul</span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-6 bg-pink-primary rounded-t" style="height: 95px;"></div>
                        <div class="w-6 bg-blue-primary rounded-t" style="height: 125px;"></div>
                    </div>
                    <span class="text-xs text-gray-600">Aug</span>
                </div>
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-6 bg-pink-primary rounded-t" style="height: 75px;"></div>
                        <div class="w-6 bg-blue-primary rounded-t" style="height: 105px;"></div>
                    </div>
                    <span class="text-xs text-gray-600">Sep</span>
                </div>
            </div>
            <!-- Legend -->
            <div class="flex items-center justify-center space-x-6 mt-4">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-pink-primary rounded-full"></div>
                    <span class="text-sm text-gray-600">Lost Items</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-primary rounded-full"></div>
                    <span class="text-sm text-gray-600">Found Items</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-pink-primary rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm text-gray-900">
                            <strong>John Dela Cruz</strong> submitted a lost item report: <strong>Black Wallet</strong>
                        </p>
                        <p class="text-xs text-gray-500">3 mins ago</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-pink-primary rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm text-gray-900">
                            <strong>Anna Reyes</strong> requested to claim the item: <strong>Red Umbrella</strong>
                        </p>
                        <p class="text-xs text-gray-500">10 mins ago</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-pink-primary rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm text-gray-900">
                            <strong>Admin Lisa</strong> flagged a report for suspicious content
                        </p>
                        <p class="text-xs text-gray-500">25 mins ago</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-pink-primary rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm text-gray-900">
                            Report <strong>Missing Backpack</strong> was approved by <strong>Admin Mark</strong>
                        </p>
                        <p class="text-xs text-gray-500">1 hour ago</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-pink-primary rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm text-gray-900">
                            <strong>Admin Lisa</strong> flagged a report for suspicious content
                        </p>
                        <p class="text-xs text-gray-500">1 hour ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Contributors Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Top Contributors</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reports</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claimed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Active</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-medal text-yellow-500 text-lg mr-2"></i>
                                <span class="text-sm font-medium text-gray-900">1</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-purple-primary text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">JaneDoe_23</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">42</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">30</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">25</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today, 10:14 AM</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <i class="fas fa-ellipsis-v"></i>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-medal text-gray-400 text-lg mr-2"></i>
                                <span class="text-sm font-medium text-gray-900">2</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-purple-primary text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">MicaReports</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">36</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">27</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">21</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yesterday</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <i class="fas fa-ellipsis-v"></i>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <i class="fas fa-medal text-orange-500 text-lg mr-2"></i>
                                <span class="text-sm font-medium text-gray-900">3</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-purple-primary text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">JohnLovesCats</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">28</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">18</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">15</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2 days ago</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <i class="fas fa-ellipsis-v"></i>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
