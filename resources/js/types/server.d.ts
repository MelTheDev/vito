export interface Server {
  id: number;
  project_id: number;
  services: {
    [key: string]: string;
  };
  user_id: number;
  name: string;
  ssh_user: string;
  ssh_users: string[];
  ip: string;
  local_ip?: string;
  port: number;
  os: string;
  type: string;
  type_data: string;
  provider: string;
  provider_id: number;
  provider_data: string;
  authentication: string;
  public_key: string;
  status: string;
  auto_update: boolean;
  available_updates: number;
  security_updates: string;
  progress?: string;
  progress_step?: string;
  updates?: string;
  last_update_check?: string;
  created_at: string;
  updated_at: string;
  status_color: 'gray' | 'success' | 'info' | 'warning' | 'danger';
  [key: string]: unknown;
}
