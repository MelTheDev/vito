import { User } from '@/types/user';

export interface SshKey {
  id: number;
  user?: User;
  name: string;
  created_at: string;
  updated_at: string;

  [key: string]: unknown;
}
