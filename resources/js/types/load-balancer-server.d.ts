export interface LoadBalancerServer {
  load_balancer_id: number;
  ip: number;
  port: number;
  weight: boolean;
  backup: string;
  created_at: string;
  updated_at: string;

  [key: string]: unknown;
}
