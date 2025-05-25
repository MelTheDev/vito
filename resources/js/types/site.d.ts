import { Server } from '@/types/server';

export interface Site {
  id: number;
  server_id: number;
  server?: Server;
  source_control_id: string;
  type: string;
  type_data: unknown;
  domain: string;
  aliases?: string[];
  web_directory: string;
  path: string;
  php_version: string;
  repository: string;
  branch: string;
  status: string;
  status_color: 'gray' | 'success' | 'info' | 'warning' | 'danger';
  port: number;
  user: string;
  url: string;
  progress: number;
  created_at: string;
  updated_at: string;
  [key: string]: unknown;
}
