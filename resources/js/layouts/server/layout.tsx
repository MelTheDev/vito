import { type NavItem } from '@/types';
import { ArrowLeftIcon, CloudUploadIcon, DatabaseIcon, HomeIcon, MousePointerClickIcon, RocketIcon, UsersIcon } from 'lucide-react';
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
    // {
    //   title: 'Firewall',
    //   href: '#',
    //   icon: FlameIcon,
    // },
    // {
    //   title: 'CronJobs',
    //   href: '#',
    //   icon: ClockIcon,
    // },
    // {
    //   title: 'Workers',
    //   href: '#',
    //   icon: ListEndIcon,
    // },
    // {
    //   title: 'SSH Keys',
    //   href: '#',
    //   icon: KeyIcon,
    // },
    // {
    //   title: 'Services',
    //   href: '#',
    //   icon: CogIcon,
    // },
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
