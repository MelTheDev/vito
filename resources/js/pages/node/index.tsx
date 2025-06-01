import { Head, usePage } from '@inertiajs/react';
import { Server } from '@/types/server';
import { PaginatedData } from '@/types';
import ServerLayout from '@/layouts/server/layout';
import HeaderContainer from '@/components/header-container';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { PlusIcon } from 'lucide-react';
import Container from '@/components/container';
import { Service } from '@/types/service';
import InstallService from '@/pages/services/components/install';
import { DataTable } from '@/components/data-table';
import { columns } from '@/pages/node/components/columns';

export default function PHP() {
  const page = usePage<{
    server: Server;
    installedVersions: PaginatedData<Service>;
  }>();

  return (
    <ServerLayout>
      <Head title={`NodeJS - ${page.props.server.name}`} />

      <Container className="max-w-5xl">
        <HeaderContainer>
          <Heading title="NodeJS" description="Here you can manage NodeJS" />
          <div className="flex items-center gap-2">
            <InstallService name="nodejs">
              <Button>
                <PlusIcon />
                <span className="hidden lg:block">Install</span>
              </Button>
            </InstallService>
          </div>
        </HeaderContainer>

        <DataTable columns={columns} paginatedData={page.props.installedVersions} />
      </Container>
    </ServerLayout>
  );
}
