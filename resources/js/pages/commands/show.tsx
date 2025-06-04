import { Head, usePage } from '@inertiajs/react';
import { Server } from '@/types/server';
import Container from '@/components/container';
import HeaderContainer from '@/components/header-container';
import Heading from '@/components/heading';
import ServerLayout from '@/layouts/server/layout';
import { DataTable } from '@/components/data-table';
import { PaginatedData } from '@/types';
import { columns } from '@/pages/commands/components/execution-columns';
import { Site } from '@/types/site';
import { CommandExecution } from '@/types/command-execution';

type Page = {
  server: Server;
  site: Site;
  executions: PaginatedData<CommandExecution>;
};

export default function Show() {
  const page = usePage<Page>();

  return (
    <ServerLayout>
      <Head title={`Executions - ${page.props.site.domain} - ${page.props.server.name}`} />

      <Container className="max-w-5xl">
        <HeaderContainer>
          <Heading title={`Command executions`} description="Here you can see the command executions" />
        </HeaderContainer>

        <DataTable columns={columns} paginatedData={page.props.executions} />
      </Container>
    </ServerLayout>
  );
}
