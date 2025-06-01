export interface Service {
  id: number;
  server_id: number;
  type: string;
  type_data: {
    extensions?: string[];
    [key: string]: unknown;
  };
  name: string;
  version: string;
  unit: number;
  is_default: boolean;
  status: string;
  status_color: 'gray' | 'success' | 'info' | 'warning' | 'danger';
  icon: string;
  created_at: string;
  updated_at: string;
  [key: string]: unknown;
}
