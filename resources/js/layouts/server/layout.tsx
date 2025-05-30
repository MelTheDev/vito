import { type NavItem } from '@/types';
import {
  ArrowLeftIcon,
  ClockIcon,
  CloudUploadIcon,
  CogIcon,
  DatabaseIcon,
  FlameIcon,
  HomeIcon,
  KeyIcon,
  ListEndIcon,
  MousePointerClickIcon,
  RocketIcon,
  UsersIcon,
} from 'lucide-react';
import { ReactNode } from 'react';
import { Server } from '@/types/server';
import ServerHeader from '@/pages/servers/components/header';
import Layout from '@/layouts/app/layout';
import { usePage, usePoll } from '@inertiajs/react';
import { Site } from '@/types/site';

export default function ServerLayout({ children }: { children: ReactNode }) {
  usePoll(7000);

  const page = usePage<{
    server: Server;
    site?: Site;
  }>();

  // When server-side rendering, we only render the layout on the client...
  if (typeof window === 'undefined') {
    return null;
  }

  const isMenuDisabled = page.props.server.status !== 'ready';

  const sidebarNavItems: NavItem[] = [
    {
      title: 'Overview',
      href: route('servers.show', { server: page.props.server.id }),
      onlyActivePath: route('servers.show', { server: page.props.server.id }),
      icon: HomeIcon,
    },
    {
      title: 'Database',
      href: route('databases', { server: page.props.server.id }),
      icon: DatabaseIcon,
      isDisabled: isMenuDisabled,
      children: [
        {
          title: 'Databases',
          href: route('databases', { server: page.props.server.id }),
          onlyActivePath: route('databases', { server: page.props.server.id }),
          icon: DatabaseIcon,
        },
        {
          title: 'Users',
          href: route('database-users', { server: page.props.server.id }),
          icon: UsersIcon,
        },
        {
          title: 'Backups',
          href: route('backups', { server: page.props.server.id }),
          icon: CloudUploadIcon,
        },
      ],
    },
    {
      title: 'Sites',
      href: route('sites', { server: page.props.server.id }),
      icon: MousePointerClickIcon,
      isDisabled: isMenuDisabled,
      children: page.props.site
        ? [
            {
              title: 'All sites',
              href: route('sites', { server: page.props.server.id }),
              onlyActivePath: route('sites', { server: page.props.server.id }),
              icon: ArrowLeftIcon,
            },
            {
              title: 'Application',
              href: route('sites.show', { server: page.props.server.id, site: page.props.site.id }),
              icon: RocketIcon,
            },
          ]
        : [],
    },
    {
      title: 'Firewall',
      href: route('firewall', { server: page.props.server.id }),
      icon: FlameIcon,
      isDisabled: isMenuDisabled,
    },
    {
      title: 'CronJobs',
      href: route('cronjobs', { server: page.props.server.id }),
      icon: ClockIcon,
      isDisabled: isMenuDisabled,
    },
    {
      title: 'Workers',
      href: route('workers', { server: page.props.server.id }),
      icon: ListEndIcon,
      isDisabled: isMenuDisabled,
    },
    {
      title: 'SSH Keys',
      href: route('server-ssh-keys', { server: page.props.server.id }),
      icon: KeyIcon,
      isDisabled: isMenuDisabled,
    },
    {
      title: 'Services',
      href: route('services', { server: page.props.server.id }),
      icon: CogIcon,
      isDisabled: isMenuDisabled,
    },
    // {
    //   title: 'Metrics',
    //   href: '#',
    //   icon: ChartPieIcon,
    // },
    // {
    //   title: 'Console',
    //   href: '#',
    //   icon: TerminalSquareIcon,
    // },
    // {
    //   title: 'Logs',
    //   href: '#',
    //   icon: LogsIcon,
    // },
    // {
    //   title: 'Settings',
    //   href: '#',
    //   icon: Settings2Icon,
    // },
  ];

  return (
    <Layout secondNavItems={sidebarNavItems} secondNavTitle={page.props.server.name}>
      <ServerHeader server={page.props.server} site={page.props.site} />

      <div>{children}</div>
    </Layout>
  );
}
