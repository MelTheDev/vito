import { Head, usePage } from '@inertiajs/react';
import { Server } from '@/types/server';
import { PaginatedData } from '@/types';
import ServerLayout from '@/layouts/server/layout';
import HeaderContainer from '@/components/header-container';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { PlusIcon } from 'lucide-react';
import Container from '@/components/container';
import { DataTable } from '@/components/data-table';
import { CronJob } from '@/types/cronjob';
import { columns } from '@/pages/cronjobs/components/columns';
import CronJobForm from '@/pages/cronjobs/components/form';

export default function CronJobIndex() {
  const page = usePage<{
    server: Server;
    cronjobs: PaginatedData<CronJob>;
  }>();

  return (
    <ServerLayout>
      <Head title={`Cron jobs - ${page.props.server.name}`} />

      <Container className="max-w-5xl">
        <HeaderContainer>
          <Heading title="Cron jobs" description="Here you can manage server's cron jobs" />
          <div className="flex items-center gap-2">
            <CronJobForm serverId={page.props.server.id}>
              <Button>
                <PlusIcon />
                <span className="hidden lg:block">Create rule</span>
              </Button>
            </CronJobForm>
          </div>
        </HeaderContainer>

        <DataTable columns={columns} paginatedData={page.props.cronjobs} />
      </Container>
    </ServerLayout>
  );
}
