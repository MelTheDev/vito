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
import { Worker } from '@/types/worker';
import { columns } from '@/pages/workers/components/columns';
import WorkerForm from '@/pages/workers/components/form';

export default function WorkerIndex() {
  const page = usePage<{
    server: Server;
    workers: PaginatedData<Worker>;
  }>();

  return (
    <ServerLayout>
      <Head title={`Workers - ${page.props.server.name}`} />

      <Container className="max-w-5xl">
        <HeaderContainer>
          <Heading title="Workers" description="Here you can manage server's workers" />
          <div className="flex items-center gap-2">
            <WorkerForm serverId={page.props.server.id}>
              <Button>
                <PlusIcon />
                <span className="hidden lg:block">Create</span>
              </Button>
            </WorkerForm>
          </div>
        </HeaderContainer>

        <DataTable columns={columns} paginatedData={page.props.workers} />
      </Container>
    </ServerLayout>
  );
}
