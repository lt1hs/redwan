export interface User {
  id: number;
  name: string;
}

export interface ActivityLog {
  id: number;
  user: User | null;
  action: 'create' | 'update' | 'delete' | 'login' | 'logout';
  module: string;
  description: string;
  created_at: string;
  details?: any;
}

export interface ActivityLogResponse {
  data: ActivityLog[];
  meta: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

export interface ActivityLogFilter {
  dateRange: {
    from: string;
    to: string;
  };
  user: number | null;
  action: string | null;
  module: string | null;
}
