<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
  item: {
    type: Object,
    required: true
  },
  level: {
    type: Number,
    default: 0
  },
  compact: {
    type: Boolean,
    default: false
  }
});

const router = useRouter();
const currentRoute = computed(() => router.currentRoute.value);
const expanded = ref(true);

function toggleExpanded() {
  expanded.value = !expanded.value;
}

const isActive = computed(() => {
  if (!props.item.to) return false;
  if (props.item.to.name) {
    return currentRoute.value.name === props.item.to.name;
  }
  const path = props.item.to.path || props.item.to;
  return currentRoute.value.path === path || currentRoute.value.path.startsWith(path + '/');
});

const hasChildren = computed(() => {
  return props.item.children && props.item.children.length > 0;
});

const isSection = computed(() => {
  return !props.item.icon && !props.item.to && !hasChildren.value;
});
</script>

<template>
  <!-- Section Header - hide in compact -->
  <div v-if="isSection && !compact" class="section-header">
    <span class="section-label">{{ item.label }}</span>
  </div>

  <!-- Menu Item with Children - hide in compact -->
  <div v-else-if="hasChildren && !compact" class="menu-group">
    <q-item
      clickable
      class="menu-item parent-item"
      @click="toggleExpanded"
    >
      <q-item-section v-if="item.icon" avatar>
        <q-icon :name="item.icon" size="18px" />
      </q-item-section>

      <q-item-section>
        <q-item-label>{{ item.label }}</q-item-label>
      </q-item-section>

      <q-item-section side>
        <q-icon :name="expanded ? 'expand_less' : 'expand_more'" size="16px" />
      </q-item-section>
    </q-item>

    <div v-if="expanded" class="submenu">
      <MenuItem
        v-for="(child, index) in item.children"
        :key="index"
        :item="child"
        :level="level + 1"
        :compact="compact"
      />
    </div>
  </div>

  <!-- Regular Menu Item -->
  <q-item
    v-else-if="!compact && !isSection"
    :to="item.to"
    clickable
    :class="['menu-item-new', { 'active-new': isActive }, `level-${level}`]"
  >
    <q-item-section v-if="item.icon" avatar>
      <q-icon :name="item.icon" size="18px" />
    </q-item-section>

    <q-item-section>
      <q-item-label>{{ item.label }}</q-item-label>
    </q-item-section>

    <q-item-section v-if="isActive" side>
      <q-icon name="chevron_right" size="16px" color="primary" />
    </q-item-section>
  </q-item>

  <!-- Compact Mode Item -->
  <q-item
    v-else-if="compact && !isSection && item.icon"
    :to="item.to"
    clickable
    :class="['menu-item-new', 'compact-item', { 'active-new': isActive }]"
  >
    <q-tooltip anchor="center right" self="center left" :offset="[10, 0]">
      {{ item.label }}
    </q-tooltip>
    
    <q-item-section avatar style="justify-content: center; min-width: auto;">
      <q-icon :name="item.icon" size="20px" />
    </q-item-section>
  </q-item>
</template>

<style lang="scss" scoped>
.section-header {
  padding: 16px 0 8px 0;
  margin: 0 16px;

  .section-label {
    color: #9ca3af;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
  }
}

:deep(.q-item.menu-item-new) {
  border-radius: 8px !important;
  margin: 2px 8px !important;
  transition: all 0.2s ease !important;
  color: #374151 !important;

  &:hover {
    background-color: #e2e8f0 !important;
    transform: translateX(3px) !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
  }

  &.active-new {
    color: #ffffff !important;
    border-left: none !important;

    .q-item__section--avatar .q-icon {
      color: #ffffff !important;
    }
  }

  &.compact-item {
    margin: 4px 12px !important;
    justify-content: center !important;
    min-height: 48px !important;
    border-radius: 12px !important;

    &:hover {
      transform: none !important;
      background-color: #e9ecef !important;
    }

    &.active-new {
      background-color: #495057 !important;
      color: #ffffff !important;
    }

    .compact-icon {
      justify-content: center !important;
      min-width: auto !important;
    }
  }

  &.level-2 {
    margin-left: 24px !important;
    margin-right: 16px !important;
    font-size: 13px !important;
    min-height: 36px !important;
  }

  .q-item__section--avatar {
    min-width: 40px !important;

    .q-icon {
      color: #6b7280 !important;
    }
  }

  .q-item__label {
    font-size: 14px !important;
    font-weight: 500 !important;
  }
}

.menu-group {
  .parent-item {
    font-weight: 600;
    background-color: #f9fafb;
  }

  .submenu {
    margin-left: 16px;
  }
}
</style>
