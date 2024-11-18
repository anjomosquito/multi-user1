<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <!-- Executive Summary -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Executive Summary</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="(stat, key) in executiveSummary.current_period" :key="key" class="bg-gray-50 p-4 rounded-lg">
          <h3 class="text-gray-600 text-sm">{{ formatLabel(key) }}</h3>
          <p class="text-2xl font-bold">{{ formatValue(key, stat) }}</p>
          <p v-if="executiveSummary.growth_rates[key + '_growth']" 
             :class="getGrowthClass(executiveSummary.growth_rates[key + '_growth'])"
             class="text-sm">
            {{ executiveSummary.growth_rates[key + '_growth'] }}% vs previous period
          </p>
        </div>
      </div>
    </section>

    <!-- Product Analysis -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Product Analysis</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
              <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Units Sold</th>
              <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
              <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
              <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Order Value</th>
              <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Growth</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="product in productAnalysis" :key="product.name">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ product.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ product.total_quantity }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">₱{{ product.total_revenue }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ product.order_count }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">₱{{ product.avg_order_value }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                <span :class="getGrowthClass(product.growth_rate)">
                  {{ product.growth_rate }}%
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Customer Analysis -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Customer Analysis</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="(segment, key) in customerAnalysis" :key="key" class="bg-gray-50 p-6 rounded-lg">
          <h3 class="text-lg font-semibold mb-4">{{ formatLabel(key) }}</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-gray-600 text-sm">Count</p>
              <p class="text-xl font-bold">{{ segment.count }}</p>
            </div>
            <div>
              <p class="text-gray-600 text-sm">Revenue</p>
              <p class="text-xl font-bold">₱{{ segment.total_revenue }}</p>
            </div>
            <div>
              <p class="text-gray-600 text-sm">Avg Order Value</p>
              <p class="text-xl font-bold">₱{{ segment.average_order_value }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Daily Trends -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Daily Trends</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sales</th>
              <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
              <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Units</th>
              <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Customers</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="day in dailyTrends" :key="day.date">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatDate(day.date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">₱{{ day.total_sales }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ day.total_orders }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ day.total_quantity }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ day.unique_customers }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: 'ComprehensiveReport',
  props: {
    executiveSummary: {
      type: Object,
      required: true
    },
    productAnalysis: {
      type: Array,
      required: true
    },
    customerAnalysis: {
      type: Object,
      required: true
    },
    dailyTrends: {
      type: Array,
      required: true
    }
  },
  methods: {
    formatLabel(key) {
      return key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
    },
    formatValue(key, value) {
      if (key.includes('total_revenue') || key.includes('average_order_value')) {
        return `₱${value}`
      }
      return value
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },
    getGrowthClass(growth) {
      const value = parseFloat(growth)
      if (value > 0) return 'text-green-600'
      if (value < 0) return 'text-red-600'
      return 'text-gray-600'
    }
  }
}
</script>
