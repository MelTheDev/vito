import { ColumnDef, Row } from '@tanstack/react-table';
import { Button } from '@/components/ui/button';
import { EyeIcon } from 'lucide-react';
import type { ServerLog } from '@/types/server-log';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { useState } from 'react';
import axios from 'axios';
import DateTime from '@/components/date-time';
import LogOutput from '@/components/log-output';
import { useQuery } from '@tanstack/react-query';

const LogActionCell = ({ row }: { row: Row<ServerLog> }) => {
  const [open, setOpen] = useState(false);

  const query = useQuery({
    queryKey: ['server-log', row.original.id],
    queryFn: async () => {
      const response = await axios.get(route('logs.show', { server: row.original.server_id, log: row.original.id }));
      return response.data;
    },
    enabled: open,
    refetchInterval: 2500,
  });

  return (
    <div className="flex items-center justify-end">
      <Dialog open={open} onOpenChange={setOpen}>
        <DialogTrigger asChild>
          <Button variant="outline" size="sm">
            <EyeIcon />
          </Button>
        </DialogTrigger>
        <DialogContent className="sm:max-w-5xl">
          <DialogHeader>
            <DialogTitle>View Log</DialogTitle>
            <DialogDescription className="sr-only">This is all content of the log</DialogDescription>
          </DialogHeader>
          <LogOutput>{query.isLoading ? 'Loading...' : query.data}</LogOutput>
          <DialogFooter>
            <Button variant="outline">Download</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export const columns: ColumnDef<ServerLog>[] = [
  {
    accessorKey: 'name',
    header: 'Event',
    enableColumnFilter: true,
  },
  {
    accessorKey: 'created_at',
    header: 'Created At',
    enableSorting: true,
    cell: ({ row }) => {
      return <DateTime date={row.original.created_at} />;
    },
  },
  {
    id: 'actions',
    enableColumnFilter: false,
    enableSorting: false,
    cell: ({ row }) => <LogActionCell row={row} />,
  },
];
