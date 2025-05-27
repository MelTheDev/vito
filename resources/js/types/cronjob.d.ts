export interface CronJob {
  id: number;
  server_id: number;
  command: string;
  user: string;
  frequency: string;
  status: string;
  status_color: 'gray' | 'success' | 'info' | 'warning' | 'danger';
  created_at: string;
  updated_at: string;
}
